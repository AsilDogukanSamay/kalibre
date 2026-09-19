/**
 * Statik onizleme katmani. Uygulamanin kendi JavaScript'ine dokunmaz;
 * yalnizca onizlemede gecerli olan iki seyi yapar.
 */
(function () {
  'use strict';

  // 1. Form devre disi. Arkasinda PHP yok; sessizce bosa calismasindansa
  //    acikca kapali olmasi dogru.
  var form = document.querySelector('#iletisim form');
  if (form) {
    form.querySelectorAll('input, textarea, button, select').forEach(function (el) {
      el.disabled = true;
    });

    var not = document.createElement('p');
    not.className = 'note-box max-w-[62ch]';
    not.textContent =
      'Bu statik bir onizlemedir: form burada kayit olusturmaz. ' +
      'Calisan surumde gonderim PHP katmaninda dogrulanip MySQL\u2019e yaziliyor ' +
      've sonuc bu tasarima uygun bir bildirimle gosteriliyor.';
    form.prepend(not);
  }

  // 2. Alt bilgideki bilgilendirmeye tek cumle eklenir.
  var bilgi = document.querySelector('.note-box');
  if (bilgi && bilgi !== document.querySelector('#iletisim .note-box')) {
    var ek = document.createElement('span');
    ek.textContent = ' Bu adres statik bir onizlemedir; calisan surum kaynak deposundadir.';
    bilgi.appendChild(ek);
  }
})();
