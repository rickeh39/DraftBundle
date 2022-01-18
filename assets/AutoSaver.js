const AutoSaver = (function() {
    let draft_id = '';
    let failResponse = '';
    let saveCallbackFunction = null;
    let cooldown;

    let editorChangeHandlerId;
    let contentEditor = null;

    function _digestForm() {
        let jsonObj = {};
        let elements = document.querySelectorAll('[data-draft-type]');
        for (let i =0; i<elements.length; i++){
            jsonObj[elements[i].getAttribute('data-draft-type')]=elements[i].value;
        }
        return jsonObj;
    }

    function _clearErrors() {
        let elements = document.querySelectorAll('[data-draft-type]');
        for (let i =0; i<elements.length; i++){
            let lastchild = elements[i].parentNode.lastElementChild;
            if (lastchild.classList.contains('error')) {
                elements[i].parentNode.removeChild(lastchild);
            }
            if (elements[i].getAttribute('data-draft-type')!=='Content'){
                elements[i].classList.remove('border-2', 'border-danger');
            } else {

            }
        }
    }

    function _attachAutosaveToInputs() {
        let elements = document.querySelectorAll('[data-draft-type]');
        for (let i =0; i<elements.length; i++){
            let inputChangeHandlerId;
            elements[i].addEventListener('keyup', function () {
                clearTimeout(inputChangeHandlerId);
                inputChangeHandlerId = setTimeout(function() {
                    autosaveDraft(contentEditor === null ? '' : contentEditor.getContent())
                }, cooldown);
            });
        }
    }

    function _init(selector, did, fr, cd = 1000) {
        draft_id = did;
        failResponse = fr;
        cooldown = cd;

        tinymce.init({
            selector: selector,
            skin: false,
            height: 500,
            content_css: false,
            plugins: 'advlist code emoticons link lists table wordcount',
            toolbar: 'bold italic | bullist numlist | link emoticons',
            setup: function(editor) {
                contentEditor = editor;
                editor.on('Paste Change input Undo Redo', function () {
                    clearTimeout(editorChangeHandlerId);
                    editorChangeHandlerId = setTimeout(function() {
                        autosaveDraft(editor.getContent())
                    }, cooldown);
                });
            }
        });

        _attachAutosaveToInputs();
    }

    function autosaveDraft(content) {
        //console.log('called: '+(new Date()));
        if (saveCallbackFunction != null){
            saveCallbackFunction('Nu aan het opslaan');
        }

        let data = _digestForm();
        data.Content = content;

        let options = {
            method: 'put',
            mode: 'cors',
            headers: {
                'X-CSRF-TOKEN': document.getElementById('article__token').value,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        };

        fetch('/draft/autosave/'+draft_id, options).then(_handleSaveResponse);
    }

    function _handleSaveResponse(response){
        _clearErrors();
        if (response.status === 200){
            response.json().then(function (jsonResponse) {
                if (saveCallbackFunction != null){
                    saveCallbackFunction(jsonResponse.updatedAt);
                }
            });
        } else {
            response.json().then(function (jsonResponse) {
                Object.keys(jsonResponse.errors).forEach(function(key) {
                    if (jsonResponse.errors[key].length>0){
                        let input = document.getElementById('article_'+key);
                        if (key!=='Content'){
                            input.classList.add('border-2', 'border-danger');
                        } else {

                        }

                        let small = document.createElement('small');
                        small.classList.add('error', 'text-danger');
                        for (let i =0; i<jsonResponse.errors[key].length; i++){
                            small.innerHTML += jsonResponse.errors[key][i]+'<br>';
                        }
                        input.parentNode.appendChild(small);
                    }
                });
                if (saveCallbackFunction != null){
                    saveCallbackFunction(failResponse);
                }
            });
        }
    }

    function _addSaveListener(callbackFunction) {
        saveCallbackFunction = callbackFunction;
    }

    return {
        init: _init,
        addSaveListener: _addSaveListener,
    }
});

module.exports = AutoSaver;