import { test } from 'node:test';
import assert from 'node:assert/strict';
import eoiEntryLocation from '../../resources/js/eoi-entry-location.js';

const divisions = { Western: { Colombo: ['Colombo', 'Homagama'], Gampaha: ['Gampaha'] } };
const records = [
    { province: 'Western', district: 'Colombo', ds_division: 'Colombo', as_centre: 'ASC A', gn_division: 'GN A' },
    { province: 'Western', district: 'Colombo', ds_division: 'Colombo', as_centre: 'ASC B', gn_division: 'GN B' },
    { province: 'Western', district: 'Colombo', ds_division: 'Homagama', as_centre: 'Other ASC', gn_division: 'Other GN' },
];

test('location choices follow parents and restore previously entered values', () => {
    const state = eoiEntryLocation(divisions, records, {
        province: 'Western', district: 'Colombo', ds_division: 'Colombo', as_centre: 'ASC A', gn_division: 'GN A',
    });
    assert.deepEqual(state.districts, ['Colombo', 'Gampaha']);
    assert.deepEqual(state.dsDivisions, ['Colombo', 'Homagama']);
    assert.deepEqual(state.asCentres, ['ASC A', 'ASC B']);
    assert.deepEqual(state.gnDivisions, ['GN A']);
    assert.equal(state.gnDivision, 'GN A');
    state.asCentre = '';
    assert.deepEqual(state.gnDivisions, ['GN A', 'GN B']);
});

test('changing parent locations clears all dependent selections', () => {
    const state = eoiEntryLocation(divisions, records, { province: 'Western', district: 'Colombo', ds_division: 'Colombo', as_centre: 'ASC A', gn_division: 'GN A' });
    state.changeDsDivision();
    assert.equal(state.asCentre, '');
    assert.equal(state.gnDivision, '');
    state.changeDistrict();
    assert.equal(state.dsDivision, '');
    assert.deepEqual(state.asCentres, []);
    state.changeProvince();
    assert.equal(state.district, '');
    assert.deepEqual(state.dsDivisions, []);
});

test('locations without existing records allow typed ASC and GND without invented suggestions', () => {
    const state = eoiEntryLocation(divisions, [], { province: 'Western', district: 'Colombo', ds_division: 'Colombo', as_centre: 'New ASC', gn_division: 'New GN' });
    assert.deepEqual(state.asCentres, []);
    assert.deepEqual(state.gnDivisions, []);
    assert.equal(state.asCentre, 'New ASC');
    assert.equal(state.gnDivision, 'New GN');
});
