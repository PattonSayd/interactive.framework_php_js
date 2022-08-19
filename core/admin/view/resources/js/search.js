/*...................................................................
..........SEARCH.....................................................
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
..........SHOH HIDE MENU SEARCH......................................
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
..........SEARCH RESULT HOVER.......................................
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


search()
searchResultHover()
showHideMenuSearch()
