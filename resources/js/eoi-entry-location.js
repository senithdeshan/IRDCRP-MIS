export default function eoiEntryLocation(divisions, records, selected = {}) {
    return {
        divisions,
        records,
        province: selected.province || '',
        district: selected.district || '',
        dsDivision: selected.ds_division || '',
        asCentre: selected.as_centre || '',
        gnDivision: selected.gn_division || '',
        get districts() {
            return Object.keys(this.divisions[this.province] || {});
        },
        get dsDivisions() {
            return this.divisions[this.province]?.[this.district] || [];
        },
        get localRecords() {
            return this.records.filter(row => row.province === this.province
                && row.district === this.district && row.ds_division === this.dsDivision);
        },
        get asCentres() {
            return [...new Set(this.localRecords.map(row => row.as_centre).filter(Boolean))].sort();
        },
        get gnDivisions() {
            return [...new Set(this.localRecords
                .filter(row => !this.asCentre || row.as_centre === this.asCentre)
                .map(row => row.gn_division).filter(Boolean))].sort();
        },
        changeProvince() {
            this.district = '';
            this.changeDistrict();
        },
        changeDistrict() {
            this.dsDivision = '';
            this.changeDsDivision();
        },
        changeDsDivision() {
            this.asCentre = '';
            this.gnDivision = '';
        },
    };
}
