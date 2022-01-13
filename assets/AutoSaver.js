const AutoSaver = (function() {
    let editorChangeHandlerId;
    let draft_id = '';

    let saveCallbackFunction = null;

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
        data.Content = content;
        console.log('LOgje voor marloes: ', data);

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
        if (response.status === 200){
            response.json().then(function (jsonResponse) {
                if (saveCallbackFunction != null && jsonResponse.updatedAt != null){
                    saveCallbackFunction(jsonResponse.updatedAt);
                }
            });
        } else {
            response.json().then(function (jsonResponse) {
                console.log(jsonResponse);
                Object.keys(jsonResponse.errors).forEach(function(key) {
                    console.log(jsonResponse.errors[key], key);
                    if (jsonResponse.errors[key].length>0){
                        let input = document.getElementById('article_'+key);
                        input.classList.add('border-2', 'border-danger');

                        let p = document.createElement('p');
                        let pstring = '';
                        for (let i =0; i<jsonResponse.errors[key].length; i++){
                            pstring += jsonResponse.errors[key][i]+'<br>';
                        }
                        p.innerHTML = pstring;
                        input.parentNode.appendChild(p);
                    }
                });
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