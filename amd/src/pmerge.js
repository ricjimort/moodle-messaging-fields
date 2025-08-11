define('local_pmerge/pmerge', ['core/ajax', 'core/log'], function(Ajax, Log) {

    function getCourseIdFromUrl() {
        try {
            var url = new URL(window.location.href);
            var id = url.searchParams.get('id');
            if (id) { return parseInt(id, 10) || 0; }
        } catch(e){}
        return 0;
    }

    function getSelectedRecipients() {
        var ids = [];

        document.querySelectorAll('input[type="checkbox"][name="user"]:checked, input[type="checkbox"][name="users[]"]:checked').forEach(function(cb){
            var uid = cb.value || cb.dataset.userid || cb.getAttribute('data-userid');
            if (uid) { ids.push(parseInt(uid, 10)); }
        });

        if (ids.length === 0) {
            document.querySelectorAll('[data-user-id]').forEach(function(el){
                var uid = el.getAttribute('data-user-id');
                if (uid) { ids.push(parseInt(uid, 10)); }
            });
        }
        ids = Array.from(new Set(ids)).filter(function(x){ return !!x; });
        return ids;
    }

    function sendPersonalized(userid, rawMsg, courseid) {
        return Ajax.call([{
            methodname: 'local_pmerge_send',
            args: { userid: userid, message: rawMsg, courseid: courseid }
        }])[0];
    }

    function hasPlaceholders(text) {
        return /\{\{(firstname|fullname|coursename)\}\}/.test(text);
    }

    function bindSendHandler() {
        document.addEventListener('click', function(e){
            var btn = e.target.closest('[data-action="send-message"], button[name="sendmessage"], button[data-region="send-message-button"]');
            if (!btn) { return; }

            var textarea = document.querySelector('textarea, [contenteditable="true"]');
            if (!textarea) { return; }

            var rawMsg = textarea.value !== undefined ? textarea.value : (textarea.innerHTML || textarea.textContent || '');

            if (!hasPlaceholders(rawMsg)) { return; }

            var recipients = getSelectedRecipients();
            if (recipients.length === 0) {
                var header = document.querySelector('[data-region="view-conversation"] [data-user-id]');
                if (header) {
                    var oneid = parseInt(header.getAttribute('data-user-id'), 10);
                    if (oneid) { recipients = [oneid]; }
                }
            }

            if (recipients.length === 0) {
                Log.debug('pmerge: no recipients detected; letting Moodle handle normally');
                return;
            }

            e.preventDefault();
            btn.disabled = true;

            var courseid = getCourseIdFromUrl();
            var pending = recipients.length;
            var errors = [];

            recipients.forEach(function(uid){
                sendPersonalized(uid, rawMsg, courseid).done(function(resp){
                    // ok
                }).fail(function(err){
                    errors.push(uid);
                }).always(function(){
                    pending--;
                    if (pending === 0) {
                        btn.disabled = false;
                        try {
                            if (textarea.value !== undefined) { textarea.value = ''; } else { textarea.innerHTML = ''; }
                        } catch(e){}
                        if (errors.length) {
                            alert('Mensajes enviados con errores para: ' + errors.join(', '));
                        } else {
                            alert('Mensajes personalizados enviados correctamente.');
                        }
                    }
                });
            });
        }, true);
    }

    function init() {
        if (window.__pmergeBound) { return; }
        window.__pmergeBound = true;

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindSendHandler);
        } else {
            bindSendHandler();
        }
        Log.debug('local_pmerge v0.1.3: bound');
    }

    return { init: init };
});
