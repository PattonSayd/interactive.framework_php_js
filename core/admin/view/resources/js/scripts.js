document.querySelector('.sitemap-button').onclick = (e) =>{
    
    e.preventDefault();
    
    createSitemap();
}

let links_counter = 0

function createSitemap() {

    links_counter++;    

    Ajax({
        data: {
            ajax: 'sitemap', 
            links_counter: links_counter
        }
    })
        .then((res) => {
            console.log('success ' + res);
        })
        .catch((res) => {
            console.log('error ' + res);
        });
    
}

createFile();  

function createFile(){
    
    let files = document.querySelectorAll('input[type=file]');

    let fileStore = [];

    if (files.length) {

        files.forEach(item => {

            item.onchange = function () {

                let multiple = false;

                let parentContainer;

                let container;

                if (item.hasAttribute('multiple')) {

                    multiple = true;

                    parentContainer = this.closest('.gallery-container') // находим родитель контейнера 
  
                    if (!parentContainer) return false;

                    container = parentContainer.querySelectorAll('.empty-container');

                    if (container.length < this.files.length) {

                        for (let index = 0; index < this.files.length - container.length; index++) {

                            let elem = document.createElement('div');

                            elem.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'mb-1', 'mr-1', 'empty-container', 'gn-gallery-container');

                            parentContainer.append(elem);
                        }

                        container = parentContainer.querySelectorAll('.empty-container');

                    }
                }

                let fileName = item.name;

                let attributeName = fileName.replace(/[\[\]]/g, ''); // вырезаем gallery[] = gallery

                for (let i in this.files) {

                    if (this.files.hasOwnProperty(i)) {

                        if (multiple) {

                            if (typeof fileStore[fileName] === "undefined") fileStore[fileName] = [];

                            let elId = fileStore[fileName].push(this.files[i]) - 1;

                            container[i].setAttribute(`data-deleteFileId-${attributeName}`, elId);

                            showImage(this.files[i], container[i]);

                            deleteNewFiles(elId, fileName, attributeName, container[i]);

                        } else {
                            container = this.closest('.img-container').querySelector('.img-show');

                            showImage(this.files[i], container);
                        }
                    }
                }
            }
        });

        function showImage(item, container) {

            let reader = new FileReader();

            container.innerHTML = ""; // если перезалъем картину

            reader.readAsDataURL(item);

            reader.onload = e => {

                container.innerHTML = '<img class="gn-img-size" src="">';

                container.querySelector('img').setAttribute('src', e.target.result);

                container.classList.remove('empty-container');
            }

        }

        function deleteNewFiles(elId, fileName, attributeName, container) {

            container.addEventListener('click', function () {

                this.remove();

                delete fileStore[fileName][elId]
            })

        }

        

        let form = document.querySelector('#add-form');        

        if(form){

            form.onsubmit = function(e){

                if(!isEmpty(fileStore)){ 
                    
                    e.preventDefault();

                    let formData = new FormData(this);
                     
                    for(let i in fileStore){
                        
                        if(fileStore.hasOwnProperty(i)){

                            formData.delete(i); 

                            let rowName = i.replace(/[/[/]]/g, '');

                            fileStore[i].forEach((item, index) => {

                                formData.append(`${rowName}[${index}]`, item)
                                
                            });
                        }
                    }

                    formData.append('ajax', 'editData');

                    Ajax({

                        url: this.getAttribute('action'),
                        type: 'post', 
                        data: formData,
                        processData: false,
                        contentType: false
                          
                    }).then(res => {
                        console.log(res);
                        try {
                            res = JSON.parse(res);

                            if(!res.success) throw new Error(); 

                            location.reload();

                        } catch (e) {
                            
                            alert('Произошла внутренняя ошибкa');
                            
                        }
                    })
                }
            }
        }
    }
}


function isEmpty(arr) {
    for(let i in arr){
        return false;
    }
    return true;
}