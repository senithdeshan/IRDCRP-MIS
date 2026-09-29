import './bootstrap';

import Alpine from 'alpinejs';
import eoiEntryLocation from './eoi-entry-location';
import agreementProgress from './agreement-progress';

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.data('eoiEntryLocation', eoiEntryLocation);
    Alpine.data('agreementProgress', agreementProgress);
    Alpine.start();
}
