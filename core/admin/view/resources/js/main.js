/*...................................................................
..........CREATE SITEMAP.............................................
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
            res = JSON.parse(res);
            console.log('success ' + res.message);
        })
        // .catch((res) => {
        //     console.log('error ' + res);
        // });

        // location.reload()
    
}


/*...................................................................
..........IS EMPTY...................................................
*/ 
function isEmpty(arr) {
    for(let i in arr){
        return false;
    }
    return true;
}


/*...................................................................
..........CHANGE PASSWORD...................................................
*/ 

function changePass(){
    document.querySelector('#change-pass-btn').addEventListener('click', function(){

        document.querySelector('.gn-shadow').classList.add('active')

        document.querySelector('.change-popup').classList.add('active')
    })

    document.querySelector('.change-popup .gn-close-btn').addEventListener('click', function(){

        document.querySelector('.change-popup').classList.remove('active')

        document.querySelector('.gn-shadow').classList.remove('active')

    })
}


function validationPass(){

    let parentElement = document.querySelector('.gn-shadow')

    let inputs = parentElement.querySelectorAll('input[type=password]')

    let button = document.querySelector('#continue-btn')   

    let form = document.querySelector('#change-form');        
    
    let error = false

    inputs.forEach(input => {

        input.addEventListener('focus', function(){

            if(!input.hasAttribute('data-error')){
                input.style.border = '1px solid #0048ff'
            }
        })

        input.addEventListener('blur', function(){
            if(!input.hasAttribute('data-error')){
                input.style.border = '1px solid #aaa'
            }
        })

        input.addEventListener('input', function(){

            if(inputs[0].value != '' && inputs[1].value != '' && inputs[2].value != ''){
                button.disabled = false
            }else{
                button.disabled = true
            } 
            
            if(inputs[2].hasAttribute('data-error')){

                inputs[2].style.border = '1px solid #0048ff';

                inputs[2].removeAttribute('data-error');      

            }else if(inputs[0].hasAttribute('data-error')){

                inputs[0].style.border = '1px solid #0048ff';

                inputs[0].removeAttribute('data-error');   
            }
            
        })

        if(form){

            form.onsubmit = function(e){

                e.preventDefault()
        
                if(inputs[2].value !== inputs[1].value){
                    inputs[2].value = ''
                    inputs[2].placeholder = 'You entered two different passwords'
                    inputs[2].style.border = '1px solid #f43f47'
                    inputs[2].classList.add('custom-placeholder')
                    inputs[2].setAttribute('data-error', 'true')
                    return false
                }

                
                Ajax({

                    url: this.getAttribute('action'),
                    type: 'post', 
                    data: {
                        current: inputs[0].value,
                        new: inputs[1].value,
                        confirm: inputs[2].value,
                        ajax: 'change_password'
                    },
                    
                }).then(res => {
                        res = JSON.parse(res);

                        if(res.error){
                            inputs[0].value = ''
                            inputs[0].placeholder = res.error
                            inputs[0].style.border = '1px solid #f43f47'
                            inputs[0].classList.add('custom-placeholder')
                            inputs[0].setAttribute('data-error', 'true')
                            return false
                        }

                        document.querySelector('.message-popup').style.opacity = '1';
                        inputs[0].value = ''
                        inputs[1].value = ''
                        inputs[2].value = ''
                        inputs[0].placeholder = ''
                        setTimeout(() => {
                            document.querySelector('.change-popup').classList.remove('active')
                            document.querySelector('.gn-shadow').classList.remove('active')
                            document.querySelector('.message-popup').style.opacity = '0';
                        },800)
                        // location.reload();
                })

            }

        }

    })
    
}



validationPass()
changePass()