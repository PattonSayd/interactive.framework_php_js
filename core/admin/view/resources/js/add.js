
/*...................................................................
..........CREATE FILE................................................
*/ 

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

                            elem.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'mb-1', 'mr-1', 'empty-container', 'gn-dotted-square');

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

                            showImage(this.files[i], container[i], function(){parentContainer.sortable({ excludedElements: '.button-div .empty-container'})});

                            deleteNewFiles(elId, fileName, attributeName, container[i]);

                        } else {
                            container = this.closest('.img-container').querySelector('.img-show');

                            showImage(this.files[i], container);
                        }
                    }
                }
            }
            
            let area = item.closest('.img-wrapper');

            if(area){

                dragAndDrop(area, item);
                
            }
            
        });

        function dragAndDrop(area, inputItem){

             ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName, index) => {

                area.addEventListener(eventName, e => {

                    e.preventDefault();

                    e.stopPropagation();

                    if(index < 2) {

                        area.style.backgroundImage = 'linear-gradient(to right, rgba(53, 91, 125, 0.25), rgba(108, 91, 123, 0.25), rgba(192, 108, 132, 0.25))';
                        
                    }else{

                        area.style.background = 'transparent';
                        
                        if(index === 3){ 

                            inputItem.files = e.dataTransfer.files;

                            inputItem.dispatchEvent(new Event('change'));
                            
                        }
                        
                    }
                    
                })
                 
             })
            
        }

        function showImage(item, container, callback) {

            let reader = new FileReader();

            container.innerHTML = ""; // если перезалъем картину

            reader.readAsDataURL(item);

            reader.onload = e => {

                container.innerHTML = '<img class="gn-img-size" src="">';

                container.querySelector('img').setAttribute('src', e.target.result);

                container.classList.remove('empty-container');

                callback && callback()
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

                createSortableImages(form)

                if(!isEmpty(fileStore)){ 
                    
                    e.preventDefault();

                    let formData = new FormData(this);
                     
                    for(let i in fileStore){
                        
                        if(fileStore.hasOwnProperty(i)){

                            formData.delete(i); 

                            let rowName = i.replace(/[\[\]]/g, '');

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


/*...................................................................
..........CHANGE MENU POSITION.......................................
*/ 
function changeMenuPosition(){

    let form = document.querySelector('#add-form');

    if(form){

        let select_parent = form.querySelector('select[name=parent_id]');
        
        let select_position = form.querySelector('select[name=menu_position]');

        if(select_parent && select_position){

            let default_parent = select_parent.value;

            let default_position = +select_position.value;

            select_parent.addEventListener('change', function(){

                let default_select = false;

                if (this.value === default_parent) default_select = true; 

                Ajax({
                    data: {
                        table : form.querySelector('input[name=table]').value,
                        'parent.id' : this.value,
                        ajax : 'change_parent',
                        iterations : !form.querySelector('#table_id') ? 1 : +!default_select // "+" переобразовывает в int

                    }
                }).then(res=>{

                    res = +res;

                    if(!res) alert('Произошла внутренняя ошибкa');

                    let new_select = document.createElement('select');

                    new_select.setAttribute('name', 'menu_position');

                    new_select.classList.add('form-control');

                    for(let i = 1; i <= res; i++){

                        let selected = default_select && i === default_position ? 'selected' : '';

                        new_select.insertAdjacentHTML('beforeend', `<option value="${i}">${i}</option>`);
                        
                    }
                      
                    select_position.before(new_select);

                    select_position.remove();

                    select_position = new_select;
                })
                
            })
            
        }
        
    }
    
}

/*...................................................................
..........BLOCK PARAMETERS...........................................
.............................checbox select all......................
*/ 
function blockParametrs(){
    
    let wraps = document.querySelectorAll('.select-wrap');

    if(wraps.length) {

        let select_all_index = []

        wraps.forEach(item => {

            let next = item.nextElementSibling;

            if(next && next.classList.contains('option-wrap')){

                item.addEventListener('click', e => {

                    if (e.target.tagName === "LABEL") return;

                    if(!e.target.classList.contains('select-all')){

                        next.slideToggle(150);
                        
                    }else{
                        let index = [...document.querySelectorAll('.select-all')].indexOf(e.target);

                        if(typeof select_all_index[index] === 'undefined') select_all_index[index] = false;

                        select_all_index[index] = !select_all_index[index];

                        next.querySelectorAll('input[type="checkbox"]').forEach(el => {

                            el.checked = select_all_index[index];

                            if(el.checked)
                                el.closest('.checkbox-container').querySelector('.select-all').classList.add('select-bold');
                            else
                                el.closest('.checkbox-container').querySelector('.select-all').classList.remove('select-bold');
                        })

                    }
                })
            }

            let select_all = item.querySelector('.select-all');

            next.querySelectorAll('input[type="checkbox"]').forEach(box => {

                box.addEventListener('change', function(){

                    if(box.checked == false){ select_all.classList.remove('select-bold')}

                    var len = [].slice.call(next.querySelectorAll('input[type="checkbox"]')).filter(function(e) { return e.checked; }).length;
                    
                    if(len === next.querySelectorAll('input[type="checkbox"]').length){select_all.classList.add('select-bold')}
                    
                })
            })
            
        })
        
    }

    
}

/*...................................................................
..........SLIDE TOGGLE...............................................
.............................slide dropdown checkbox.................
*/ 
Element.prototype.slideToggle = function(time, callback){

    let _time = typeof time === 'number' ? time : 400

    callback = typeof time === 'function' ? time : callback

    if(getComputedStyle(this)['display'] === 'none'){

        this.style.transition = null;

        this.style.overflow = 'hidden';

        this.style.maxHeight = 0;

        this.style.display = 'block';  

        this.style.transition = _time + 'ms';   

        this.style.maxHeight = this.scrollHeight + 'px';  
        
        setTimeout(() => {

            callback && callback()
            
        }, _time)

    }else{
        this.style.transition = _time + 'ms';  

        this.style.maxHeight = 0;

        setTimeout(() => {

            this.style.transition = null;

            this.style.display = 'none';  
            
            callback && callback()
            
        }, _time)

    }
    
}


/*...................................................................
..........SHOW SELECT CHECKBOX.......................................
....................................loading selected chechboxes......
*/
function showSelectCheckbox(){
    
    window.addEventListener('DOMContentLoaded', function(){ 

        document.querySelectorAll('.select-all').forEach(el => {

            let input = el.closest('.checkbox-container').querySelectorAll('.checked-box');
           
            let check = true;
            
            input.forEach(box => {

                if(!box.checked) check = box.checked  
                
            })
            
            if(check) {
               
                el.classList.add('select-bold') 
            }

        })

        
     })
    
}



/*...................................................................
..........DRAG'N'DROP BLOCKS.........................................
............................moving blocks............................
*/

let swapBnt = document.querySelectorAll('#swap')
let fieldset = document.querySelector('#fieldset')
let parent = fieldset.closest('#add-form').querySelector('#parentBlock')

let btn = true

document.querySelector('#changeBlokcs').addEventListener('click', function(e){

    e.preventDefault(); 

    if(btn){

        let block = document.createElement('button');

        parentBlock.insertAdjacentHTML('beforeend', `<button id="saveBtn" onclick="createSortableBlocks(event)" type="submit" class="btn float-right mr-2" style="background-color: #e2ecf5; color:#71899f; border: 1px solid #8fa1af; border-bottom:3px solid #8fa1af">Save bloks</button>`); 
    }

    swapBnt.forEach(el => {

        if (el.style.display === "none") {

            el.style.display = "block";
            
            Array.prototype.forEach.call(fieldset.children, child => {
                
                if(child.hasAttribute("data", "prevent")){
                    child.removeAttribute("data")
                }
            })  

            if(fieldset){

                [...fieldset.children].forEach(item => {
                    item.sortable()
                })
            }

            btn = false

        } else {

            if(!btn){
                parentBlock.removeChild(document.querySelector('#saveBtn'))
            }
            
            el.style.display = "none";

            Array.prototype.forEach.call(fieldset.children, child => {
                
                if(!child.hasAttribute("data", "prevent")){
                    child.setAttribute("data", "prevent");
                }
            })  

            btn = true
        }
    })
})

/*...................................................................
..........SORTABLE BLOCKS............................................
............................sort blocks..............................
*/ 


function createSortableBlocks(event){

    event.preventDefault()

    let form = document.querySelector('#add-form')

    let bloks = form.querySelectorAll('.jsElement__sortableBlock')

    let formData = new FormData(form);

    if(bloks.length){

        bloks.forEach((item, index) => {

            let name = item.getAttribute('name')

            if(name){

                name = name.replace(/\[\]/g, '');                

                formData.append(`${'sortable'}[${index}]`, name)
            }
            
        })

        formData.append('ajax', 'bloks');

        Ajax({

            url: form.getAttribute('action'),
            type: 'post', 
            data: formData,
            processData: false,
            contentType: false
              
        }).then(res => {

            try {
                res = JSON.parse(res);

                if(!res.success) throw new Error(); 

                location.reload();

            } catch (e) {
                
                alert('Произошла внутренняя ошибкa при сортировке блоков');
                
            }
        })
        
    }

    
}
/*...................................................................
..........DRAG'N'DROP................................................
............................moving images on the gallery.............
*/ 
Element.prototype.sortable = (function() {

    var dragEl, nextEl;

    function _unDraggable(elements){

        if(elements && elements.length){

            for(let i = 0; i < elements.length; i++){

                if(!elements[i].hasAttribute('draggable')){

                    elements[i].draggable = false;

                    _unDraggable(elements[i].children);
                    
                } 
            } 
        }  
    }

    function _onDragStart(e){

        e.stopPropagation();

        dragEl = e.target;

        if(dragEl.parentNode.hasAttribute("data", "prevent")) e.preventDefault();

        nextEl = dragEl.nextSibling;
        
        this.tempTarget = null

        e.dataTransfer.dropEffect = 'move';

        if(this.className.match('/center|left|right/')){

            if(dragEl.querySelector('#swap')){

                dragEl.querySelector('#swap').addEventListener('dragover', e => {

                    dragEl.style.border = "1px solid #b4b7b4"
    
                    this.addEventListener('dragover', _onDragOver, false)
    
                    this.addEventListener('dragend', _onDragEnd, false)
                })                
            }            
        }else{

            this.addEventListener('dragover', _onDragOver, false)

            this.addEventListener('dragend', _onDragEnd, false)
            
        }       
    }

    function _onDragOver(e){
  
        e.preventDefault();
        e.stopPropagation();

        e.dataTransfer.dropEffect = 'move';

        let target;

        if(e.target !== this.tempTarget){

            this.tempTarget = e.target;

            target = e.target.closest('[draggable=true]');
        }

        if(target && target !== dragEl && target.parentElement === this){

            let rect = target.getBoundingClientRect();

            let next = (e.clientY - rect.top)/(rect.bottom - rect.top) > .5;

            target.style.transform = "translateY("+next+"px)"

            this.insertBefore(dragEl, next && target.nextElementSibling || target);
        }       
    }

    function _onDragEnd(e) {

        e.preventDefault();

        this.removeEventListener('dragover', _onDragOver, false)

        this.removeEventListener('dragover', _onDragEnd, false)

        dragEl.style.border = ""

        if(nextEl !== dragEl.nextsibling){

            this.onUpdate && this.onUpdate(dragEl)
        }
    }
    
    return function(options){

        options = options || {}

        if(options.prevent){ options.prevent.preventDefault()}

        // options.prevent.preventDefault()

        this.onUpdate = options.stop || null;

        let excludedElements = options.excludedElements && options.excludedElements.split(/,*\s+/) || null; //

        [...this.children].forEach(item => {

            let draggable = true;
            
            if(excludedElements){

                for(let i in excludedElements){

                    if(excludedElements.hasOwnProperty(i) && item.matches(excludedElements[i])) {

                        draggable = false;

                        break
                        
                    }
                } 
            }

            item.draggable = draggable;

            _unDraggable(item.children)
            
        })

        this.removeEventListener('dragstart', _onDragStart, false)

        this.addEventListener('dragstart', _onDragStart, false)
        
    }
})();


let galleries = document.querySelectorAll('.gallery-container');

if(galleries.length){
    galleries.forEach(item => {
        item.sortable({
            excludedElements: '.button-div .empty-container',
            stop: function (dragEl){console.log(dragEl)}
        })
    })
}



/*...................................................................
..........CREATE IMAGE SORTABLE......................................
.............................sorting image block & save db...........
*/  
function createSortableImages(form){

    if(form){

        let sortable = form.querySelectorAll('input[type=file][multiple]');

        if(sortable.length){

            sortable.forEach(item => {

                let container = item.closest('.gallery-container');

                let name = item.getAttribute('name');

                if(name && container){
                    
                    name = name.replace(/\[\]/g, '');

                    let inputSorting = form.querySelector(`input[name="js-sorting-image[${name}]"]`);

                    if(!inputSorting){

                        inputSorting = document.createElement('input');

                        inputSorting.setAttribute('type', 'hidden');
                        
                        inputSorting.name = `js-sorting-image[${name}]`;

                        form.append(inputSorting);
                        
                    }

                    let res = []

                    for(let i in container.children){

                        if(container.children.hasOwnProperty(i)){

                            if(!container.children[i].matches('.button-div') && !container.children[i].matches('.empty-container')){

                                if(container.children[i].tagName === 'A'){

                                    res.push(container.children[i].querySelector('img').getAttribute('src'));
                                    
                                }else{

                                    res.push(container.children[i].getAttribute(`data-deletefileid-${name}`))
                                    
                                }
                                
                            }
                            
                        }
                        
                    }

                    inputSorting.value = JSON.stringify(res)
                }
                
            })
            
        }
    
    }
    
}

/*...................................................................
..........ADD EMPTY BLOK.............................................
*/ 

function addEmptyBlok(){
    let parent = document.querySelector('#fieldset')

    let left_block = parent.querySelector('.left');
    let right_block = parent.querySelector('.right');

    let left_block_sum = 0
    let right_block_sum = 0

    Array.prototype.forEach.call(left_block.children, child => {
        left_block_sum += child.offsetHeight
    });

    Array.prototype.forEach.call(right_block.children, child => {
        right_block_sum += child.offsetHeight
    });


    if(left_block_sum < right_block_sum){

        let diff = right_block_sum -  left_block_sum; 

        let block = document.createElement('div');

        block.classList.add('col-12' , 'gn-block-style');
        block.style.height = diff+'px';

        left_block.append(block);
    }else{

        let diff = left_block_sum - right_block_sum 

        let block = document.createElement('div');

        block.classList.add('col-12' , 'gn-block-style');
        block.style.height = diff+'px'

        right_block.append(block);
    }

    
}

addEmptyBlok()
showSelectCheckbox()
createFile();  
blockParametrs()
changeMenuPosition()