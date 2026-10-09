const AREA_CODE_FIELDS = {
  province: ['province_code', 'ADM1_PCODE', 'areaCode', 'area_code'],
  district: ['district_code', 'ADM2_PCODE', 'areaCode', 'area_code', 'amphur_code', 'amphoe_code'],
  subdistrict: ['subdistrict_code', 'ADM3_PCODE', 'areaCode', 'area_code', 'tambon_code'],
};

const AREA_NAME_FIELDS = {
  province: ['province_name', 'ADM1_TH', 'areaName', 'area_name'],
  district: ['district_name', 'amphur_name', 'amphoe_name', 'ADM2_TH', 'areaName', 'area_name'],
  subdistrict: ['subdistrict_name', 'tambon_name', 'ADM3_TH', 'areaName', 'area_name'],
};

const normalizeCode = (value, length = 2) =>
  String(value ?? '').replace(/^TH/i, '').padStart(length, '0');

const isZeroCode = (value) => value == null || /^0+$/.test(String(value));

const getAreaCode = (record, level) => {
  const codeLength = { province: 2, district: 4, subdistrict: 6 }[level] || 2;
  for (const field of AREA_CODE_FIELDS[level] || AREA_CODE_FIELDS.province) {
    const candidate = record[field];
    if (candidate != null && String(candidate).replace(/^TH/i, '').length === codeLength) {
      return normalizeCode(candidate, codeLength);
    }
  }

  const provinceCode = normalizeCode(record.province_code);
  const amphurCode = normalizeCode(record.amphur_code);
  const tambonCode = normalizeCode(record.tambon_code);
  if (level === 'district' && record.province_code != null && record.amphur_code != null) {
    return `${provinceCode}${amphurCode}`;
  }
  if (level === 'subdistrict' && record.province_code != null &&
    record.amphur_code != null && record.tambon_code != null) {
    return `${provinceCode}${amphurCode}${tambonCode}`;
  }
  return level === 'province' && record.province_code != null ? provinceCode : null;
};

const getAreaName = (record, level) => {
  for (const field of AREA_NAME_FIELDS[level] || AREA_NAME_FIELDS.province) {
    if (record[field] != null) return record[field];
  }
  return 'ไม่ระบุชื่อพื้นที่';
};

const isRecordAtLevel = (record, level) => {
  if (level === 'province') {
    return isZeroCode(record.amphur_code) && isZeroCode(record.tambon_code);
  }
  if (level === 'district') {
    return isZeroCode(record.tambon_code);
  }
  return !isZeroCode(record.tambon_code);
};

const isWithinParentArea = (record, level, parentAreaCode) => {
  if (!parentAreaCode) return true;
  if (level === 'district') {
    return normalizeCode(record.province_code) === normalizeCode(parentAreaCode);
  }
  if (level === 'subdistrict') {
    return getAreaCode(record, 'district') === normalizeCode(parentAreaCode, 4);
  }
  return true;
};

const toNumber = (value) => {
  const number = Number(value);
  return Number.isFinite(number) ? number : 0;
};

export const getNoneProduceDisplayRows = (data, level, parentAreaCode = null) => {
  const rows = Array.isArray(data) ? data : [];
  let levelRows = rows.filter((record) => isRecordAtLevel(record, level));

  if (levelRows.length === 0 && level !== 'subdistrict') {
    levelRows = rows;
  }

  const grouped = new Map();
  levelRows
    .filter((record) => isWithinParentArea(record, level, parentAreaCode))
    .forEach((record) => {
      const areaCode = getAreaCode(record, level);
      if (!areaCode) return;

      const existing = grouped.get(areaCode);
      if (existing) {
        existing.total_farmers += toNumber(record.total_farmers);
        existing.total_plant += toNumber(record.total_plant);
        return;
      }

      grouped.set(areaCode, {
        ...record,
        area_code: areaCode,
        area_name: getAreaName(record, level),
        total_farmers: toNumber(record.total_farmers),
        total_plant: toNumber(record.total_plant),
      });
    });

  return [...grouped.values()];
};
