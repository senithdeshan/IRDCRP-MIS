import { test } from 'node:test';
import assert from 'node:assert/strict';
import agreementProgress from '../../resources/js/agreement-progress.js';

test('calculates stage and cumulative tranche percentages without adding revised budget', () => {
    const state = agreementProgress({ investment: { own: 1000 }, tr1: { own: 100 }, tr2: { loan: 200 }, tr3: { grant: 300 }, revised: { own: 1500 } });
    assert.equal(state.percentageLabel('investment'), '100.00%');
    assert.equal(state.percentageLabel('tr2'), '20.00%');
    assert.equal(state.percentageLabel('tr2', true), '30.00%');
    assert.equal(state.percentageLabel('tr3', true), '60.00%');
    assert.equal(state.percentageLabel('revised'), '150.00%');
    assert.equal(state.barWidth('revised'), '100%');
    state.amounts.investment.own = 2000;
    assert.equal(state.percentageLabel('tr3', true), '30.00%');
});

test('handles missing budget and decimal currency amounts', () => {
    const state = agreementProgress();
    assert.equal(state.percentage('tr1'), null);
    assert.equal(state.barWidth('tr1'), '0%');
    state.amounts.investment = { own: '0.10', loan: '0.20', grant: '' };
    state.amounts.tr1.own = '0.15';
    assert.equal(state.total('investment'), '0.30');
    assert.equal(state.percentageLabel('tr1'), '50.00%');
});
