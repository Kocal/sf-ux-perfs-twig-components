import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    submit() {
        // Deferred so the Select controller has synced its hidden <select> before the form is serialized.
        setTimeout(() => this.element.requestSubmit());
    }
}
