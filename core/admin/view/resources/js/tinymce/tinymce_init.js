function MCEInit(element, height = 400){

    tinymce.init({

        language: 'ru',
        mode: 'exact',
        elements: element || tinyMceDefaultArea,
        height: height,
        gecko_spellcheck: true,
        relative_urls: false,
        plugins: [
            "advlist autolink lists link image charmap print preview hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars code fullscreen",
            "insertdatetime media nonbreaking save table directionality",
            "emoticons template paste textpattern media imagetools"
        ],
        toolbar: "insertfile undo redo | styleselect | bold italic | forecolor backcolor emoticons | " +
            "alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | " +
            "formatselect fontsizeselect | code media emoticons ",  
        image_advtab: true,
        image_titles: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        images_reuse_filename: true,
        image_upload_handler: function(file, success, fail){

           
            
        },
        file_picker_callback: function(callback , value, meta){

            let input = document.createElement('input')

            input.setAttribute('type', 'file')

            input.setAttribute('accept', 'image/*')

            input.click()

            input.onchange = function(){

                let reader = new FileReader

                reader.readAsDataURL(this.files[0])

                reader.onload = () => {

                    let blobCache = tinymce.activeEditor.editorUpload.blobCache

                    let base64 = reader.result.split(',')[1]

                    let blobInfo = blobCache.create(this.files[0].name, this.files[0], base64)

                    blobCache.add(blobInfo)

                    callback(blobInfo.blobUri(), {title: this.files[0].name})
                    
                }
                
            }
            
        }

    })
    
}

let mce_element = document.querySelectorAll('input.input-tinyMCE')

if(mce_element.length) {

    mce_element.forEach(item => {

        item.onchange = () => {

            let blockContent = item.closest('.center')

            let textArea = item.closest('.element-tinyMCE').querySelector('textarea')

            let textAreaName = textArea.getAttribute('name')

            if(textAreaName){

                if(item.checked){

                    MCEInit(textAreaName, blockContent ? 400 : 300)
                    
                }else{

                    tinymce.remove(`[name="${textAreaName}"]`)

                    if(!blockContent) textArea.value = textArea.value.replace(/<\/?[^>]+(>|$)/g, '')
                    
                }
                
            }
            
        }
        
    })
    
}
