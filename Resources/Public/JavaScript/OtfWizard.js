/**
 * Module: TYPO3/CMS/Otf/OtfWizard
 */
define(['TYPO3/CMS/Core/Ajax/AjaxRequest', 'TYPO3/CMS/Backend/Notification', 'TYPO3/CMS/Backend/Severity'], (function(AjaxRequest, Notification, Severity) {
  'use strict';

  class OtfWizard extends HTMLElement {
    constructor() {
      super();

      this.onBlur = () => {
        let payload = {
          value: this.inputElement.value,
          table: this.getAttribute('table') || '',
          field: this.getAttribute('field') || '',
          uid: this.getAttribute('uid') || '',
          pid: Number(this.getAttribute('pid') || 0),
          evaluations: this.getAttribute('evaluations') || ''
        };

        const customElement = this;

        (new AjaxRequest(TYPO3.settings.ajaxUrls.form_otf)).post(payload).then(async function (response) {
          const data = await response.resolve();
          if (!data.success) {
            Notification.warning('On-the-fly evaluation failed');
          } else if (data.evaluationHint) {
            const evaluationHint = document.createElement('span');
            evaluationHint.classList.add('label', 'label-' + Severity.getCssClass(data.evaluationHint.severity));
            evaluationHint.innerText = data.evaluationHint.message;
            customElement.append(evaluationHint);
          }
        }).catch(function () {
          Notification.error('An error occurced');
        });
      }

      this.onFocus = () => {
        const evaluationHint = this.querySelector('span');
        if (evaluationHint === null) {
          return
        }
        evaluationHint.parentElement.removeChild(evaluationHint);
      }
    }

    connectedCallback() {
      this.inputElement = document.querySelector('input[data-formengine-input-name="' + this.getAttribute('element') + '"]');
      if (this.inputElement === null) {
        return;
      }

      this.inputElement.addEventListener('blur', this.onBlur);
      this.inputElement.addEventListener('focus', this.onFocus);
    }

    disconnectedCallback() {
      if (this.inputElement === null) {
        return
      }
      this.inputElement.removeEventListener('blur', this.onBlur);
      this.inputElement.removeEventListener('focus', this.onFocus);
    }
  }

  window.customElements.define('typo3-formengine-otf-wizard', OtfWizard);
}));
