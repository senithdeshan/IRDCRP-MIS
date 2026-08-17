@once
    <script>
        function adminDivisionPicker(divisions, selectedProvince = '', selectedDistrict = '', selectedDsDivision = '') {
            return {
                divisions,
                province: selectedProvince || '',
                district: selectedDistrict || '',
                dsDivision: selectedDsDivision || '',
                get districts() {
                    if (!this.province || !this.divisions[this.province]) {
                        return [];
                    }

                    return Object.keys(this.divisions[this.province]);
                },
                get dsDivisions() {
                    if (!this.province || !this.district || !this.divisions[this.province]?.[this.district]) {
                        return [];
                    }

                    return this.divisions[this.province][this.district];
                },
            };
        }
    </script>
@endonce
