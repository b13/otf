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
          evaluations: this.getAttribute('evaluations') || '',
          returnUrl: location.href
        };

        const customElement = this;
        const inputElement = this.inputElement;

        (new AjaxRequest(TYPO3.settings.ajaxUrls.form_otf)).post(payload).then(async function (response) {
          const data = await response.resolve();
          if (!data.success) {
            Notification.warning('On-the-fly evaluation failed');
          } else if (data.evaluationHint) {
            // This is a workaround to prevent the input from being resized to the length of the hint
            const elementWrapperMaxWidth = inputElement.closest('.form-control-wrap')?.style.maxWidth;
            const inputWrapper = inputElement.closest('.form-wizards-element');
            if (elementWrapperMaxWidth && inputWrapper) {
                inputWrapper.style.maxWidth = elementWrapperMaxWidth;
            }
            // Append the hint
            const evaluationHint = document.createElement('span');
            evaluationHint.classList.add('label', 'label-' + Severity.getCssClass(data.evaluationHint.severity));
            if (data.evaluationHint.markup) {
              evaluationHint.innerHTML = data.evaluationHint.message;
            } else {
              evaluationHint.innerText = data.evaluationHint.message;
            }
            customElement.append(evaluationHint);
          }
        }).catch(function () {
          Notification.error('An error occurced');
        });
      }

      this.onFocus = () => {
        // Remove the manually set "max-width" property from the input field wrapper
        const inputWrapper = this.inputElement.closest('.form-wizards-element');
        if (inputWrapper && inputWrapper.style.maxWidth) {
            inputWrapper.style.removeProperty('max-width');
        }
        // Remove the hint
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
