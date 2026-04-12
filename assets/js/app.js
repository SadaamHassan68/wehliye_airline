/* Wehliye Airline — client-side helpers (forms, AJAX) */
(function () {
  'use strict';
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  var editModal = document.getElementById('editFlightModal');
  if (editModal) {
    editModal.addEventListener('show.bs.modal', function (event) {
      var btn = event.relatedTarget;
      if (!btn) {
        return;
      }
      var set = function (id, attr) {
        var el = document.getElementById(id);
        if (el) {
          el.value = btn.getAttribute(attr) || '';
        }
      };
      set('edit_flight_id', 'data-flight-id');
      var routeSel = document.getElementById('edit_route_id');
      if (routeSel) {
        var rid = btn.getAttribute('data-route-id');
        routeSel.value = rid && rid !== '0' ? rid : '0';
      }
      set('edit_flight_no', 'data-flight-no');
      set('edit_origin', 'data-origin');
      set('edit_destination', 'data-destination');
      set('edit_departure_time', 'data-departure');
      set('edit_arrival_time', 'data-arrival');
      set('edit_aircraft', 'data-aircraft');
      set('edit_capacity', 'data-capacity');
      set('edit_base_price', 'data-base-price');
      var statusSel = document.getElementById('edit_status');
      if (statusSel) {
        var st = btn.getAttribute('data-status');
        if (st) {
          statusSel.value = st;
        }
      }
    });
  }

  var scheduleRouteSel = document.getElementById('schedule_route_id');
  if (scheduleRouteSel) {
    scheduleRouteSel.addEventListener('change', function () {
      var opt = scheduleRouteSel.options[scheduleRouteSel.selectedIndex];
      if (!opt) {
        return;
      }
      var wrap = document.getElementById('addFlightFormCollapse');
      if (!wrap) {
        return;
      }
      var fn = wrap.querySelector('input[name="flight_no"]');
      var ac = wrap.querySelector('input[name="aircraft"]');
      var bp = wrap.querySelector('input[name="base_price"]');
      if (fn) {
        fn.value = opt.getAttribute('data-flight-no') || '';
      }
      if (ac) {
        ac.value = opt.getAttribute('data-airline') || '';
      }
      if (bp) {
        var v = opt.getAttribute('data-base-price');
        bp.value = v !== null && v !== undefined ? v : '';
      }
    });
  }
})();
