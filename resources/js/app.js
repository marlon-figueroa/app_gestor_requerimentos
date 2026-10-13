import './bootstrap';
import $ from './libs/jquery';
import { initSelect2 } from './libs/select2';
import DataTable, { serverTable, reloadTables } from './libs/datatables';
import { initDatePickers, TempusDominus, DateTime } from './libs/tempus-dominus';
import { notify, registerDeleteHandler } from './libs/helpers';
import { initTheme, setTheme, getStoredTheme, resolveTheme } from './libs/theme';
import { initSidebar } from './libs/sidebar';
import { initFeatherIcons, refreshFeatherIcons, feather } from './libs/feather';
import { Swal, initAlerts, alertMessage, confirmAction, success, error, warning, info } from './libs/alerts';

window.Swal = Swal;

window.App = {
    $,
    DataTable,
    serverTable,
    reloadTables,
    initSelect2,
    initDatePickers,
    TempusDominus,
    DateTime,
    notify,
    alert: alertMessage,
    confirm: confirmAction,
    success,
    error,
    warning,
    info,
    setTheme,
    getTheme: getStoredTheme,
    resolveTheme,
    feather,
    refreshFeatherIcons,
};

initTheme();
initSidebar();
initFeatherIcons();
initSelect2();
initDatePickers();
registerDeleteHandler();
initAlerts();

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new window.bootstrap.Tooltip(el));
