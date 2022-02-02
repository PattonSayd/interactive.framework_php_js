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

                    parentContainer = this.closest('.gallery_container') // находим родитель контейнера 

                    if (!parentContainer)
                        return false;

                    container = parentContainer.querySelectorAll('.empty_container');

                    if (container.length < this.files.length) {

                        for (let index = 0; index < this.files.length - container.length; index++) {

                            let elem = document.createElement('div');

                            elem.classList.add('vg-dotted-square', 'vg-center', 'empty_container');

                            parentContainer.append(elem);
                        }

                        container = parentContainer.querySelectorAll('.empty_container');

                    }
                }

                let fileName = item.name;

                let attributeName = fileName.replace(/[\[\]]/g, '');

                for (let i in this.files) {

                    if (this.files.hasOwnProperty(i)) {

                        if (multiple) {

                            if (typeof fileStore[fileName] === "undefined")
                                fileStore[fileName] = [];

                            let elId = fileStore[fileName].push(this.files[i]) - 1;

                            container[i].setAttribute(`data-deleteFileId-${attributeName}`, elId);

                            showImage(this.files[i], container[i]);

                            deleteNewFiles(elId, fileName, attributeName, container[i]);

                        } else {
                            container = this.closest('.img_container').querySelector('.img_show');

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

                container.innerHTML = '<img class="img_item" src="">';

                container.querySelector('img').setAttribute('src', e.target.result);

                container.classList.remove('empty_container');

            }

        }

        function deleteNewFiles(elId, fileName, attributeName, container) {

            container.onclick = function () {

                this.remove();

                delete fileStore[fileName][elId]
            }

        }

        let form = document.querySelector('#main-form');        

        if(form){

            form.onsubmit = function(e){

                if(!isEmpty(fileStore)){ // если массив не пуст

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


