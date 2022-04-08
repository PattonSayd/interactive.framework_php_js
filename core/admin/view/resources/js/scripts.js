/*...................................................................
.....................................................................
..........CREATE SITEMAP.............................................
.....................................................................
.....................................................................
*/ 
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
/*...................................................................
.....................................................................
..........CREATE FILE................................................
.....................................................................
.....................................................................
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

                createJsSortable(form)

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

/*...................................................................
.....................................................................
..........IS EMPTY...................................................
.....................................................................
.....................................................................
*/ 
function isEmpty(arr) {
    for(let i in arr){
        return false;
    }
    return true;
}

/*...................................................................
.....................................................................
..........CHANGE MENU POSITION.......................................
.....................................................................
.....................................................................
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
.....................................................................
..........BLOCK PARAMETERS...........................................
.............................checbox select all......................
.....................................................................
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
.....................................................................
..........SLIDE TOGGLE...............................................
.............................slide dropdown checkbox.................
.....................................................................
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
.....................................................................
..........SHOW SELECT CHECKBOX.......................................
....................................loading selected chechboxes......
.....................................................................
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
.....................................................................
..........SHOH HIDE MENU SEARCH......................................
.....................................................................
.....................................................................
*/ 
function showHideMenuSearch(){

    let search = document.querySelector('.gn-search');

    let icon = document.querySelector('.gn-search-icon');

    let input = document.querySelector('.gn-search-input');

    let dropdown = document.querySelector('.gn-dropdown');

    icon.addEventListener('click', () =>{

        search.classList.toggle('active');
        input.focus()

    })
   
   

    input.addEventListener('blur', e =>{

        if(e.relatedTarget && e.relatedTarget.tagName === 'A') return;

        input.value = ''

        dropdown.innerText = ''

        search.classList.remove('active');

        dropdown.classList.remove('active');
        
    })

    input.addEventListener('input', () =>{
        
        if(input.value.length <= 1){
            
            dropdown.classList.remove('active');
        }
    })
    
}

/*...................................................................
.....................................................................
..........CHANGE MENU POSITION.......................................
.....................................................................
.....................................................................
*/ 
let searchResultHover = (() => {

    let dropdown = document.querySelector('.gn-dropdown');

    let search_input = document.querySelector('.gn-search-input');

    let default_input_value = null;

    function searchKeyDouwn(e){ 

        if((document.querySelector('.gn-form-search').classList.contains('gn-search-reverse')) || 
            (e.key !== 'ArrowUp' && e.key !== 'ArrowDown')) return;
        
        let drop = [...dropdown.children];

        if(drop.length){

            e.preventDefault();
            
            let active_item = dropdown.querySelector('.gn-search-active');

            let active_index = active_item ? drop.indexOf(active_item) : -1;

            if(e.key === 'ArrowUp')
                active_index = active_index <= 0 ? drop.length - 1 : --active_index;
            else
                active_index = active_index === drop.length - 1 ? 0 : ++active_index;
 
            drop.forEach(item => item.classList.remove('gn-search-active'));

            drop[active_index].classList.add('gn-search-active');

            search_input.value = drop[active_index].innerText.replace(/\(.+?\)\s*$/, '');
            
        }
    }

    function setDefaultValue(e){
        
        search_input.value = default_input_value;
        
    }
    
    dropdown.addEventListener('mouseleave', setDefaultValue);

    window.addEventListener('keydown', searchKeyDouwn);

    
    return () => {

        setTimeout(() =>{

            default_input_value = search_input.value;
           
        },0);

        if(dropdown.children.length){

            let children = [...dropdown.children];

            children.forEach(item => {

                item.addEventListener('mouseover', () => {

                    children.forEach(el => el.classList.remove('gn-search-active'));
                    
                    item.classList.add('gn-search-active');

                    search_input.value = item.innerText.replace(/\(.+?\)\s*$/, '');
                })                   
            })
        }
    }
    
})()


/*...................................................................
.....................................................................
..........DRAG'N'DROP................................................
............................moving images on the gallery.............
.....................................................................
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

        this.tempTarget = null

        dragEl = e.target;

        nextEl = dragEl.nextSibling;

        e.dataTransfer.dropEffect = 'move';

        if(this.className.match('/center|left|right/')){

            if(dragEl.querySelector('#swap')){

                dragEl.querySelector('#swap').addEventListener('dragover', e => {

                    dragEl.style.border = "1px solid #01b389"
    
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

let fieldset = document.querySelector('#fieldset');

if(fieldset){

    [...fieldset.children].forEach(item => {

        item.sortable()
    })
    
}

/*...................................................................
.....................................................................
..........CREATE JS SORTABLE.........................................
.............................sorting moved images....................
.....................................................................
*/ 
function createJsSortable(form){

    if(form){

        let sortable = form.querySelectorAll('input[type=file][multiple]');

        if(sortable.length){

            sortable.forEach(item => {

                let container = item.closest('.gallery-container');

                let name = item.getAttribute('name');

                if(name && container){
                    
                    name = name.replace(/\[\]/g, '');

                    let inputSorting = form.querySelector(`input[name="js-sorting[${name}]"]`);

                    if(!inputSorting){

                        inputSorting = document.createElement('input');

                        inputSorting.setAttribute('type', 'hidden');
                        
                        inputSorting.name = `js-sorting[${name}]`;

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
.....................................................................
..........SEARCH.....................................................
.....................................................................
.....................................................................
*/ 
function search(){

    let searchInput = document.querySelector('input[name=search]');

    if(searchInput){

        searchInput.oninput = () => {

            if(searchInput.value.length > 1){

                Ajax({
                        data: {
                            data: searchInput.value,
                            table: document.querySelector('input[name="search_table"]').value,
                            ajax: 'search'
                        }
                    
                    
                }).then(res => {
                    try{
                        console.log(res)
                        res = JSON.parse(res)

                        let res_dropdown = document.querySelector('.gn-dropdown')

                        let counter = res.length > 20 ? 20 : res.length

                        if(res_dropdown){

                            res_dropdown.innerHTML = '';

                            for(let i = 0; i < counter; i++){
                                
                                res_dropdown.insertAdjacentHTML('beforeend', `<a href="${res[i]['alias']}">${res[i]['name']}</a>`)
                            }

                            searchResultHover();

                            if(res_dropdown.children.length > 0) res_dropdown.classList.add('active');
                            else res_dropdown.classList.remove('active');                         
                        }

                    }catch(e){

                        alert('Ошибка в системе поиска по административной панели')
                        
                    }
                })
                
            }
            
        }
        
    }
    
}


/*...................................................................
.....................................................................
..........CREATE BLOCK SORTABLE......................................
.............................sorting block & save db.................
.....................................................................
*/ 
function createBlockSortable(form){

    if(form){

        let sortable = form.querySelectorAll('input[type=file][multiple]');

        if(sortable.length){

            sortable.forEach(item => {

                let container = item.closest('.gallery-container');

                let name = item.getAttribute('name');

                if(name && container){
                    
                    name = name.replace(/\[\]/g, '');

                    let inputSorting = form.querySelector(`input[name="js-sorting[${name}]"]`);

                    if(!inputSorting){

                        inputSorting = document.createElement('input');

                        inputSorting.setAttribute('type', 'hidden');
                        
                        inputSorting.name = `js-sorting[${name}]`;

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

search()
searchResultHover()
showHideMenuSearch()
showSelectCheckbox()
createFile();  
blockParametrs()
changeMenuPosition()