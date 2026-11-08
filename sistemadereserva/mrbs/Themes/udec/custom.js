/**
 * Interacciones Modernas UdeC
 * - Módulo 1: Hover Cards (Tooltips flotantes de reservas con metadatos)
 * - Módulo 2: Leyenda Interactiva (Filtro por tipo de horario en 1 clic)
 * Universidad de Concepción | Campus Los Ángeles
 */

(function($) {
  'use strict';

  $(document).ready(function() {
    $('#udecRoomFilterBar').remove();
    initHoverCards();
    initInteractiveLegend();
    initCurrentTimeLine();
    initRoomMetadataBanner();
  });

  // Re-inicializar cuando MRBS refresca la grilla por AJAX
  $(document).ajaxComplete(function(event, xhr, settings) {
    if (settings && settings.url && settings.url.indexOf('index.php') !== -1) {
      $('#udecRoomFilterBar').remove();
      initHoverCards();
      initInteractiveLegend();
      initCurrentTimeLine();
      initRoomMetadataBanner();
    }
  });


  /* ==========================================================================
     1. HOVER CARDS (TOOLTIPS FLOTANTES DE RESERVAS)
     ========================================================================== */
  function initHoverCards() {
    let $card = $('#udecHoverCard');
    if ($card.length === 0) {
      $card = $('<div id="udecHoverCard"></div>').appendTo('body');
    }

    let hideTimer = null;
    const bookingSelector = '.dwm_main .booking a, .dwm_main .booked a, .dwm_main .booking_list a, .dwm_main .booking_list > div a, #month_main .booking a';

    $(document).off('mouseenter.udechc mouseleave.udechc', bookingSelector);

    $(document).on('mouseenter.udechc', bookingSelector, function(e) {
      clearTimeout(hideTimer);

      const $el = $(this);
      const name = $el.data('name') || $el.text().trim();
      const room = $el.data('room') || '';
      const time = $el.data('time') || '';
      const user = $el.data('user') || '';
      const typeName = $el.data('type-name') || '';
      const desc = $el.data('desc') || '';
      const typeCode = $el.data('type') || '';

      if (!name) return;

      if ($el.attr('title')) {
        $el.data('saved-title', $el.attr('title'));
        $el.removeAttr('title');
      }

      let html = '<div class="udec-hc-header">';
      html += '  <span class="udec-hc-header-title"><i class="fa-solid fa-graduation-cap"></i> UdeC Reserva</span>';
      if (typeName) {
        html += '  <span class="udec-hc-badge ' + typeCode + '">' + $('<div>').text(typeName).html() + '</span>';
      }
      html += '</div>';

      html += '<div class="udec-hc-body">';
      html += '  <div class="udec-hc-title">' + $('<div>').text(name).html() + '</div>';
      html += '  <div class="udec-hc-grid">';

      if (time) {
        let timeFormatted = time.trim();
        if (!timeFormatted.toLowerCase().endsWith('hrs') && !timeFormatted.toLowerCase().endsWith('hr')) {
          timeFormatted += ' hrs';
        }
        html += '    <div class="udec-hc-row">';
        html += '      <span class="udec-hc-icon"><i class="fa-regular fa-clock"></i></span>';
        html += '      <span class="udec-hc-val"><strong>Horario:</strong> ' + $('<div>').text(timeFormatted).html() + '</span>';
        html += '    </div>';
      }

      if (room) {
        html += '    <div class="udec-hc-row">';
        html += '      <span class="udec-hc-icon"><i class="fa-solid fa-location-dot"></i></span>';
        html += '      <span class="udec-hc-val"><strong>Recinto:</strong> ' + $('<div>').text(room).html() + '</span>';
        html += '    </div>';
      }

      if (user) {
        html += '    <div class="udec-hc-row">';
        html += '      <span class="udec-hc-icon"><i class="fa-regular fa-user"></i></span>';
        html += '      <span class="udec-hc-val"><strong>Solicitante:</strong> ' + $('<div>').text(user).html() + '</span>';
        html += '    </div>';
      }

      html += '  </div>';

      if (desc && desc.trim() !== '') {
        html += '  <div class="udec-hc-desc">';
        html += '    <i class="fa-solid fa-circle-info"></i> ' + $('<div>').text(desc).html();
        html += '  </div>';
      }

      html += '</div>';

      $card.html(html);
      positionCard($card, $el);
      $card.addClass('active');
    });

    $(document).on('mouseleave.udechc', bookingSelector, function() {
      const $el = $(this);
      if ($el.data('saved-title')) {
        $el.attr('title', $el.data('saved-title'));
      }

      hideTimer = setTimeout(function() {
        $card.removeClass('active');
      }, 100);
    });

    // Hover Card institucional para cabeceras de salas (Vista Día y Vista <Todas>)
    const roomHeaderSelector = '.dwm_main th[data-room] a';
    $(document).off('mouseenter.udecroomhc mouseleave.udecroomhc', roomHeaderSelector);

    $(document).on('mouseenter.udecroomhc', roomHeaderSelector, function() {
      clearTimeout(hideTimer);

      const $a = $(this);
      const $th = $a.closest('th[data-room]');
      const roomName = ($th.attr('data-room-name') || $a.clone().children().remove().end().text()).trim();
      const roomCap = parseInt($th.attr('data-capacity') || '0', 10);
      const roomDesc = ($th.attr('data-description') || '').trim();

      if (!roomName) return;

      if ($a.attr('title')) {
        $a.data('saved-title', $a.attr('title'));
        $a.removeAttr('title');
      }

      let html = '<div class="udec-hc-header">';
      html += '  <span class="udec-hc-header-title"><i class="fa-solid fa-door-open"></i> Ficha de Sala</span>';
      if (roomCap > 0) {
        html += '  <span class="udec-hc-badge" style="background:#E89D1E;color:#002B49;"><i class="fa-solid fa-users"></i> ' + roomCap + ' pers.</span>';
      }
      html += '</div>';

      html += '<div class="udec-hc-body">';
      html += '  <div class="udec-hc-title">' + $('<div>').text(roomName).html() + '</div>';
      html += '  <div class="udec-hc-grid">';

      if (roomCap > 0) {
        html += '    <div class="udec-hc-row">';
        html += '      <span class="udec-hc-icon"><i class="fa-solid fa-user-group"></i></span>';
        html += '      <span class="udec-hc-val"><strong>Capacidad:</strong> ' + roomCap + ' personas</span>';
        html += '    </div>';
      }

      if (roomDesc) {
        const parts = roomDesc.split('|').map(s => s.trim()).filter(Boolean);
        parts.forEach(function(part) {
          if (part.toLowerCase().startsWith('tipo:')) {
            const val = part.substring(5).trim();
            html += '    <div class="udec-hc-row">';
            html += '      <span class="udec-hc-icon"><i class="fa-solid fa-layer-group"></i></span>';
            html += '      <span class="udec-hc-val"><strong>Modalidad:</strong> ' + $('<div>').text(val).html() + '</span>';
            html += '    </div>';
          } else {
            html += '    <div class="udec-hc-row">';
            html += '      <span class="udec-hc-icon"><i class="fa-solid fa-plug-circle-check"></i></span>';
            html += '      <span class="udec-hc-val"><strong>Equipamiento:</strong> ' + $('<div>').text(part).html() + '</span>';
            html += '    </div>';
          }
        });
      }

      html += '  </div>';
      html += '</div>';

      $card.html(html);
      positionCard($card, $a);
      $card.addClass('active');
    });

    $(document).on('mouseleave.udecroomhc', roomHeaderSelector, function() {
      const $a = $(this);
      if ($a.data('saved-title')) {
        $a.attr('title', $a.data('saved-title'));
      }
      hideTimer = setTimeout(function() {
        $card.removeClass('active');
      }, 100);
    });

    function positionCard($card, $target) {
      const offset = $target.offset();
      const targetWidth = $target.outerWidth();
      const targetHeight = $target.outerHeight();
      const cardWidth = $card.outerWidth() || 310;
      const cardHeight = $card.outerHeight() || 180;
      const winWidth = $(window).width();
      const winHeight = $(window).height();
      const scrollTop = $(window).scrollTop();
      const scrollLeft = $(window).scrollLeft();

      let top = offset.top - cardHeight - 10;
      let left = offset.left + (targetWidth / 2) - (cardWidth / 2);

      if (top < scrollTop + 10) {
        top = offset.top + targetHeight + 10;
      }
      if (left + cardWidth > scrollLeft + winWidth - 15) {
        left = scrollLeft + winWidth - cardWidth - 15;
      }
      if (left < scrollLeft + 15) {
        left = scrollLeft + 15;
      }

      $card.css({
        top: Math.round(top) + 'px',
        left: Math.round(left) + 'px'
      });
    }
  }


  /* ==========================================================================
     2. LEYENDA INTERACTIVA Y FILTRADO POR CARRERA (OPCIÓN B)
     ========================================================================== */
  function initInteractiveLegend() {
    const $legend = $('.color_key');
    if ($legend.length === 0) return;

    if ($legend.find('.udec-legend-header').length === 0) {
      const headerHtml = `
        <div class="udec-legend-header">
          <span class="udec-legend-title">
            <i class="fa-solid fa-filter"></i> Filtro por tipo de horario:
          </span>
          <button type="button" id="udecResetFilter" class="udec-reset-filter" style="display:none;">
            <i class="fa-solid fa-rotate-left"></i> Restablecer horario (Ver todas)
          </button>
        </div>
      `;
      $legend.prepend(headerHtml);
    }

    const $badges = $legend.find('> div:not(.udec-legend-header)');

    $badges.each(function() {
      const $badge = $(this);
      const classes = ($badge.attr('class') || '').split(/\s+/);
      const typeCode = classes.find(c => c && c !== 'active-filter' && c.indexOf('udec') === -1) || classes[0];
      $badge.attr('data-type-code', typeCode);
      $badge.attr('title', 'Haz clic para ver solo las reservas de ' + $badge.text().trim());
    });

    $badges.off('click.udecfilter').on('click.udecfilter', function(e) {
      e.preventDefault();
      const $clickedBadge = $(this);
      const typeCode = $clickedBadge.attr('data-type-code');

      if ($clickedBadge.hasClass('active-filter')) {
        clearCareerFilter();
        return;
      }

      $badges.removeClass('active-filter');
      $clickedBadge.addClass('active-filter');
      $legend.addClass('color_key_dimmed');
      $('#udecResetFilter').fadeIn(150);

      const $allBookings = $('.dwm_main .booking, .dwm_main .booked a, .dwm_main .booking_list > div, .dwm_main .booking_list a, #month_main .booking');
      $allBookings.each(function() {
        const $b = $(this);
        const bType = $b.data('type') || 
                      ($b.is('a') ? $b.attr('class') : ($b.find('a').data('type') || $b.find('a').attr('class'))) || '';
        
        const isMatch = (bType === typeCode) || 
                        $b.hasClass(typeCode) || 
                        ($b.find('.' + typeCode).length > 0) || 
                        ($b.find('[data-type="' + typeCode + '"]').length > 0);

        if (isMatch) {
          $b.addClass('highlighted-by-filter').removeClass('dimmed-by-filter');
          $b.closest('.booking').addClass('highlighted-by-filter').removeClass('dimmed-by-filter');
          $b.closest('.booking_list > div').addClass('highlighted-by-filter').removeClass('dimmed-by-filter');
        } else {
          $b.addClass('dimmed-by-filter').removeClass('highlighted-by-filter');
          $b.closest('.booking').addClass('dimmed-by-filter').removeClass('highlighted-by-filter');
          $b.closest('.booking_list > div').addClass('dimmed-by-filter').removeClass('highlighted-by-filter');
        }
      });

      const $grid = $('.dwm_main');
      if ($grid.length > 0) {
        const gridTop = $grid.offset().top - 90;
        if ($(window).scrollTop() > gridTop + 350) {
          $('html, body').animate({ scrollTop: gridTop }, 300);
        }
      }
    });

    $('#udecResetFilter').off('click.udecclear').on('click.udecclear', function(e) {
      e.preventDefault();
      clearCareerFilter();
    });

    function clearCareerFilter() {
      $badges.removeClass('active-filter');
      $legend.removeClass('color_key_dimmed');
      $('#udecResetFilter').fadeOut(150);
      $('.dwm_main .booking, .dwm_main .booked a, .dwm_main td.booked, .dwm_main div.booking, .dwm_main .booking_list > div, .dwm_main .booking_list a').removeClass('highlighted-by-filter dimmed-by-filter');
    }
  }


  /* ==========================================================================
     3. LÍNEA DE HORA ACTUAL EN VIVO ("AHORA MISMO")
     ========================================================================== */
  let currentTimeTimer = null;

  function initCurrentTimeLine() {
    clearInterval(currentTimeTimer);
    updateCurrentTimeLine();
    currentTimeTimer = setInterval(updateCurrentTimeLine, 60000);
    $(window).off('resize.udectime').on('resize.udectime', updateCurrentTimeLine);
  }

  function updateCurrentTimeLine() {
    const $grid = $('.dwm_main');
    const $tableContainer = $('.table_container');
    if ($grid.length === 0 || $tableContainer.length === 0) {
      $('#udecCurrentTimeIndicator').remove();
      return;
    }

    // No aplica a vista mensual
    if ($('#month_main').length > 0) {
      $('#udecCurrentTimeIndicator').remove();
      return;
    }

    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const todayIso = `${year}-${month}-${day}`;

    let $todayTh = $('thead th[data-date="' + todayIso + '"]');
    const isDayView = ($('#day_main').length > 0);
    const dayViewDate = $('input[name="page_date"]').val() || '';

    let colLeft = 0;
    let colWidth = 0;

    if ($todayTh.length > 0) {
      // Vista semanal: ubicamos exactamente la columna de hoy
      const thOffset = $todayTh.offset();
      const containerOffset = $tableContainer.offset();
      colLeft = thOffset.left - containerOffset.left + $tableContainer.scrollLeft();
      colWidth = $todayTh.outerWidth();
    } else if (isDayView && dayViewDate === todayIso) {
      // Vista diaria del día de hoy: cruza las salas
      const $firstDataTd = $('tbody tr').first().find('td').first();
      const $lastDataTd = $('tbody tr').first().find('td').last();
      if ($firstDataTd.length > 0 && $lastDataTd.length > 0) {
        const startLeft = $firstDataTd.offset().left - $tableContainer.offset().left + $tableContainer.scrollLeft();
        const endRight = $lastDataTd.offset().left + $lastDataTd.outerWidth() - $tableContainer.offset().left + $tableContainer.scrollLeft();
        colLeft = startLeft;
        colWidth = endRight - startLeft;
      }
    } else {
      // Hoy no está en la vista actual
      $('#udecCurrentTimeIndicator').hide();
      return;
    }

    if (colWidth <= 0) {
      $('#udecCurrentTimeIndicator').hide();
      return;
    }

    // Calcular minutos transcurridos en el día
    const currentMins = now.getHours() * 60 + now.getMinutes();

    // Parsear filas de horarios
    const slots = [];
    $('tbody tr').each(function() {
      const $tr = $(this);
      const $th = $tr.find('th').first();
      if ($th.length === 0) return;
      const text = $th.text().trim();
      const matches = text.match(/(\d{1,2}):(\d{2})/g);
      if (matches && matches.length >= 1) {
        const p1 = matches[0].split(':');
        const start = parseInt(p1[0], 10) * 60 + parseInt(p1[1], 10);
        let end = start + 45;
        if (matches.length >= 2) {
          const p2 = matches[1].split(':');
          end = parseInt(p2[0], 10) * 60 + parseInt(p2[1], 10);
        }
        slots.push({
          $tr: $tr,
          start: start,
          end: end
        });
      }
    });

    if (slots.length === 0) {
      $('#udecCurrentTimeIndicator').hide();
      return;
    }

    const firstSlot = slots[0];
    const lastSlot = slots[slots.length - 1];

    // Si la hora actual está fuera del horario académico (+/- 30 mins)
    if (currentMins < firstSlot.start - 30 || currentMins > lastSlot.end + 30) {
      $('#udecCurrentTimeIndicator').hide();
      return;
    }

    let calculatedY = null;

    for (let i = 0; i < slots.length; i++) {
      const s = slots[i];
      if (currentMins >= s.start && currentMins <= s.end) {
        const fraction = (currentMins - s.start) / Math.max(1, (s.end - s.start));
        const rowTop = s.$tr.offset().top - $tableContainer.offset().top + $tableContainer.scrollTop();
        calculatedY = rowTop + (s.$tr.outerHeight() * fraction);
        break;
      } else if (i < slots.length - 1 && currentMins > s.end && currentMins < slots[i + 1].start) {
        // En el receso entre dos bloques
        const breakFrac = (currentMins - s.end) / Math.max(1, (slots[i + 1].start - s.end));
        const prevBottom = (s.$tr.offset().top + s.$tr.outerHeight()) - $tableContainer.offset().top + $tableContainer.scrollTop();
        const nextTop = slots[i + 1].$tr.offset().top - $tableContainer.offset().top + $tableContainer.scrollTop();
        calculatedY = prevBottom + ((nextTop - prevBottom) * breakFrac);
        break;
      }
    }

    // Ligeramente antes del primer bloque
    if (calculatedY === null && currentMins < firstSlot.start) {
      calculatedY = firstSlot.$tr.offset().top - $tableContainer.offset().top + $tableContainer.scrollTop();
    }
    // Ligeramente después del último bloque
    if (calculatedY === null && currentMins > lastSlot.end) {
      calculatedY = (lastSlot.$tr.offset().top + lastSlot.$tr.outerHeight()) - $tableContainer.offset().top + $tableContainer.scrollTop();
    }

    if (calculatedY === null) {
      $('#udecCurrentTimeIndicator').hide();
      return;
    }

    let $indicator = $('#udecCurrentTimeIndicator');
    if ($indicator.length === 0) {
      $indicator = $(`
        <div id="udecCurrentTimeIndicator" class="udec-current-time-indicator">
          <div class="udec-time-badge">
            <span class="udec-time-dot"></span>
            <span class="udec-time-text"></span>
          </div>
          <div class="udec-time-line"></div>
        </div>
      `).appendTo($tableContainer);
    }

    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    $indicator.find('.udec-time-text').text(`${hh}:${mm}`);

    $indicator.css({
      top: Math.round(calculatedY) + 'px',
      left: Math.round(colLeft) + 'px',
      width: Math.round(colWidth) + 'px'
    }).show();
  }


  /* ==========================================================================
     4. CINTILLO INSTITUCIONAL DE FICHA TÉCNICA DE SALA (AFORO Y EQUIPAMIENTO)
     ========================================================================== */
  function initRoomMetadataBanner() {
    $('#udecRoomMetaBanner').remove();

    const $thead = $('.dwm_main thead[data-current-room-name]');
    if ($thead.length === 0) return;

    const roomName = ($thead.attr('data-current-room-name') || '').trim();
    const roomCap = parseInt($thead.attr('data-current-room-cap') || '0', 10);
    const roomDesc = ($thead.attr('data-current-room-desc') || '').trim();

    if (!roomName && !roomDesc && !roomCap) return;

    const $tableContainer = $('.table_container').first();
    if ($tableContainer.length === 0) return;

    let capHtml = '';
    if (roomCap > 0) {
      capHtml = `
        <span class="udec-rmb-pill udec-rmb-cap">
          <i class="fa-solid fa-users"></i> Capacidad: <strong>${roomCap} pers.</strong>
        </span>
      `;
    }

    let descHtml = '';
    if (roomDesc) {
      const parts = roomDesc.split('|').map(s => s.trim()).filter(Boolean);
      parts.forEach(function(part) {
        if (part.toLowerCase().startsWith('tipo:')) {
          descHtml += `
            <span class="udec-rmb-pill udec-rmb-type">
              <i class="fa-solid fa-layer-group"></i> ${$('<div>').text(part).html()}
            </span>
          `;
        } else {
          descHtml += `
            <span class="udec-rmb-pill udec-rmb-equip">
              <i class="fa-solid fa-plug-circle-check"></i> ${$('<div>').text(part).html()}
            </span>
          `;
        }
      });
    }

    const bannerHtml = `
      <div id="udecRoomMetaBanner" class="udec-room-meta-banner">
        <div class="udec-rmb-title">
          <i class="fa-solid fa-door-open"></i>
          <span>Ficha de Recinto: <strong>${$('<div>').text(roomName).html()}</strong></span>
        </div>
        <div class="udec-rmb-pills">
          ${capHtml}
          ${descHtml}
        </div>
      </div>
    `;

    $tableContainer.before(bannerHtml);
  }

})(jQuery);
