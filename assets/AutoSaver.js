const AutoSaver = (function() {
    let editorChangeHandlerId;
    let draft_id = '';
    let isCreated = true;

    let saveCallbackFunction = null;

    function _getDraftId(){
        return draft_id;
    }

    function _digestForm() {
        let jsonObj = {};
        let elements = document.querySelectorAll('[data-draft-type]');
        for (let i =0; i<elements.length; i++){
            jsonObj[elements[i].getAttribute('data-draft-type')]=elements[i].value;
        }
        return jsonObj;
    }

    function _init(selector, did) {
        draft_id = did;
        if (draft_id.length===0){
            isCreated = false;
        }

        tinymce.init({
            selector: selector,
            skin: false,
            height: 500,
            content_css: false,
            plugins: 'advlist code emoticons link lists table wordcount',
            toolbar: 'bold italic | bullist numlist | link emoticons',
            setup: function(editor) {
                editor.on('Paste Change input Undo Redo', function () {
                    clearTimeout(editorChangeHandlerId);
                    editorChangeHandlerId = setTimeout(function() {
                        autosaveDraft(editor.getContent())
                    }, 1000);
                });
            }
        });
    }

    function autosaveDraft(content) {
        if (saveCallbackFunction != null){
            saveCallbackFunction('Nu aan het opslaan');
        }


        let data = _digestForm();
        data.content = content;

        let options = {
            method: 'put',
            mode: 'cors',
            headers: {
                'X-CSRF-TOKEN': document.getElementById('article__token').value,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        };

        let urlExtra = draft_id.length!==0 ? 'autosave/'+draft_id : 'firstautosave';

        if ((draft_id.length>0) === isCreated){
            isCreated=true;
            fetch('/draft/'+urlExtra, options).then(_handleSaveResponse);
        }
    }

    function _handleSaveResponse(response){
        if (response.status === 200){
            response.json().then(function (jsonResponse) {
                if (jsonResponse.newDraftId!=null) {
                    draft_id = jsonResponse.newDraftId;
                }
                if (saveCallbackFunction != null && jsonResponse.updatedAt != null){
                    saveCallbackFunction(jsonResponse.updatedAt);
                }
            })
        } else {
            alert(response.status+" | "+response.statusText)
        }
    }

    function _addSaveListener(callbackFunction) {
        saveCallbackFunction = callbackFunction;
    }

    return {
        init: _init,
        addSaveListener: _addSaveListener,
        getDraftId: _getDraftId,
    }
});

module.exports = AutoSaver;