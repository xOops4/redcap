/**
 * TemporalAutoOpen
 *
 * Purpose
 * - Listen to blur/change events on mapped temporal fields for the current event.
 * - Open the adjudication modal when it’s closed; otherwise refresh its content.
 * - Ensure the fetch uses unsaved form values so temporal windows reflect user edits.
 *
 * Workflow
 * 1) bind() locates the form and temporal fields for the current event.
 * 2) On blur/change, the debounced handler compares current vs previous value.
 * 3) If a meaningful change occurred, the class resolves the record id and then:
 *    - modal.open(recordId, { hasTemporalFields: true, autoFetch: true }) when closed
 *    - modal.fetchData(recordId, { forceRefresh: true }) when open
 */

// No top-level helpers; class encapsulates behavior and state.

/**
 * Register temporal auto-open behavior for the current page.
 * - Exposes global modal instance and refresh helper
 * - Binds field listeners on data entry pages
 * @param {object} options
 * @param {object} options.adjudicationModal
 * @param {Record<string,string[]>} options.temporalFieldsByEvent
 */
export class TemporalAutoOpen {
  /**
   * @param {object} options
   * @param {object} options.adjudicationModal - AdjudicationModal instance
   * @param {Record<string,string[]>} options.temporalFieldsByEvent - map of event_id => field names
   * @param {string} [options.eventId] - current event id (if known)
   * @param {string} [options.recordId] - current record id (if known)
   * @param {string} [options.tablePkFieldName] - primary key field name to discover record on the form
   * @param {number} [options.debounceMs] - debounce interval (default 400ms)
   */
  constructor({ adjudicationModal, temporalFieldsByEvent, eventId = '', recordId = '', tablePkFieldName = '', debounceMs = 400 }) {
    this.modal = adjudicationModal;
    this.temporalFieldsByEvent = temporalFieldsByEvent || {};
    this.eventId = String(eventId || '');
    this.recordId = String(recordId || '');
    this.tablePkFieldName = String(tablePkFieldName || '');
    this.debounceMs = debounceMs;

    this.handleFieldChange = this._debounce(this._handleFieldChange.bind(this), this.debounceMs);
  }

  /**
   * Attach listeners to temporal fields for the current event on the current form.
   * No-ops if the form or fields are not present.
   */
  bind() {
    const form = document.querySelector('form#form');
    if (!form) return;
    const fieldList = (this.eventId && this.temporalFieldsByEvent?.[this.eventId]) ? this.temporalFieldsByEvent[this.eventId] : [];
    if (!fieldList.length) return;

    fieldList.forEach((name) => {
      const selector = `form#form input[name="${name}"], form#form select[name="${name}"], form#form textarea[name="${name}"]`;
      document.querySelectorAll(selector).forEach((el) => {
        el.dataset.ddpPrevVal = (el.value || '').trim();
        el.addEventListener('blur', this.handleFieldChange);
        el.addEventListener('change', this.handleFieldChange);
      });
      // Repeating instances (best-effort selectors)
      const repeatSelector = `form#form [name^="${name}___"], form#form [name*="[${name}]"]`;
      document.querySelectorAll(repeatSelector).forEach((el) => {
        el.dataset.ddpPrevVal = (el.value || '').trim();
        el.addEventListener('blur', this.handleFieldChange);
        el.addEventListener('change', this.handleFieldChange);
      });
    });
  }

  /**
   * Determine if auto-open is enabled via localStorage flag.
   * @returns {boolean}
   */
  isEnabled() {
    const pref = localStorage.getItem('ddp_auto_open_on_temporal');
    return pref === null || pref === 'on';
  }

  /**
   * Get the current record id from constructor, or discover via primary key field on the form.
   * @returns {string|null}
   */
  getRecordId() {
    if (this.recordId) return this.recordId;
    if (!this.tablePkFieldName) return null;
    const el = document.querySelector(`form#form [name="${this.tablePkFieldName}"]`);
    const val = (el?.value || '').trim();
    return val || null;
  }

  /**
   * Debounce wrapper
   * @private
   */
  _debounce(fn, wait) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); };
  }

  /**
   * Change/blur event handler for temporal fields.
   * @private
   */
  _handleFieldChange(ev) {
    if (!this.isEnabled()) return;
    const el = ev.target;
    const val = (el.value || '').trim();
    const prev = el.dataset.ddpPrevVal;
    el.dataset.ddpPrevVal = val;
    if (!val || val === prev) return;

    const recordId = this.getRecordId();
    if (!recordId) return;

    try {
      if (this.modal?.isOpen) {
        // If selections exist, modal will prompt via its own logic on refresh
        this.modal.fetchData(recordId, { forceRefresh: true });
      } else {
        this.modal.open(recordId, { hasTemporalFields: true, autoFetch: true });
      }
    } catch (e) {
      console.warn('TemporalAutoOpen: failed to open/refresh modal', e);
    }
  }
}
