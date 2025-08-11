define(['core/ajax', 'core/str', 'core/log'], function(Ajax, Str, Log) {

    function getCourseIdFromUrl() {
        try {
            var url = new URL(window.location.href);
            var id = url.searchParams.get('id');
            return id ? (parseInt(id, 10) || 0) : 0;
        } catch (e) { return 0; }
    }

    function getSelectedRecipients() {
        var ids = [];
        document.querySelectorAll('input[type="checkbox"][name="users[]"]:checked').forEach(function(cb){
            var uid = parseInt(cb.value || cb.dataset.userid || cb.getAttribute('data-userid'), 10);
            if (uid && ids.indexOf(uid) === -1) { ids.push(uid); }
        });
        return ids;
    }

    function sendOne(userid, message, courseid, subject) {
        return Ajax.call([{
            methodname: 'local_pmerge_send',
            args: { userid: userid, message: message, courseid: courseid, subject: subject }
        }])[0];
    }

    function bindSendHandler() {
        var courseid = getCourseIdFromUrl();

        var btn = document.querySelector('[data-action="message-send"], button[name="sendmessage"], .pmerge-send');
        if (!btn) {
            Log.debug('local_pmerge: send button not found on this page');
            return;
        }

        btn.addEventListener('click', function(e){
            if (e.defaultPrevented) { return; }

            var ids = getSelectedRecipients();
            if (!ids.length) { return; }

            e.preventDefault();

            var textarea = document.querySelector('textarea[name="message"], textarea[id*="message"]');
            var subject = (document.querySelector('input[name="subject"]') || {}).value || '';
            var text = textarea ? textarea.value : '';
            if (!text) { return; }

            Promise.all(ids.map(function(uid){
                return sendOne(uid, text, courseid, subject);
            })).then(function(){
                Log.debug('local_pmerge: messages sent: ' + ids.length);
            }).catch(function(err){
                Log.error(err);
            });
        }, { once: false });
    }

    function init() {
        if (window.__pmergeBound) { return; }
        window.__pmergeBound = true;

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindSendHandler);
        } else {
            bindSendHandler();
        }
        Log.debug('local_pmerge: init ok');
    }

    return { init: init };
});
