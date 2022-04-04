<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>

    
    <style>
      @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400&display=swap');

        *, *:before, *:after {
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            margin: 0;
        }
        .container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        .container:hover .top:before, .container:active .top:before, .container:hover .bottom:before, .container:active .bottom:before, .container:hover .top:after, .container:active .top:after, .container:hover .bottom:after, .container:active .bottom:after {
            margin-left: 200px;
            transform-origin: -200px 50%;
            transition-delay: 0s;
        }
        .container:hover .center, .container:active .center {
            opacity: 1;
            transition-delay: 0.2s;
        }
        .top:before, .bottom:before, .top:after, .bottom:after {
            content: '';
            display: block;
            position: absolute;
            width: 200vmax;
            height: 200vmax;
            top: 50%;
            left: 50%;
            margin-top: -100vmax;
            transform-origin: 0 50%;
            transition: all 0.5s cubic-bezier(0.445, 0.05, 0, 1);
            z-index: 10;
            opacity: 0.65;
            transition-delay: 0.2s;
        }
        .top:before {
            transform: rotate(45deg);
            background: #e46569;
        }
        .top:after {
            transform: rotate(135deg);
            background: #ecaf81;
        }
        .bottom:before {
            transform: rotate(-45deg);
            background: #60b8d4;
        }
        .bottom:after {
            transform: rotate(-135deg);
            background: #3745b5;
        }
        .center {
            position: absolute;
            width: 400px;
            height: 400px;
            top: 50%;
            left: 50%;
            margin-left: -200px;
            margin-top: -200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.445, 0.05, 0, 1);
            transition-delay: 0s;
            color: #333;
        }
        .center input {
            width: 100%;
            padding: 15px;
            margin: 5px;
            border-radius: 1px;
            border: 1px solid #ccc;
            font-family: inherit;
        }

        .center input[type=submit] {
            width: 30%;
            padding: 8px;
            margin: 5px;
            border-radius: 1px;
            border: 1px solid #ccc;
            font-family: inherit;
            cursor: pointer;
        }
        
        .center input[type=submit]:hover {
            background-color: #d8d9cf;
        }

        form{
            text-align:center 
        }
        .alert{
            font-family: inherit;
            font-size: 12px;
            color: #e46569;
            text-align: center;
        }

    </style>    

</head>
<body>
    
    <div class="container" method="post" onclick="onclick">
        <div class="top"></div>
        <div class="bottom"></div>
        <div class="center">
            <h2>Please Sign In</h2>
            <?php if(isset($_SESSION['res']['answer']))
                echo '<span class="alert">' . $_SESSION['res']['answer'] . '</span>';
                unset($_SESSION['res']);
            ?>
            <form action="<?=PATH . $admin_path?>/login" method="post">
                <input type="text" name="login" placeholder="login" />
                <input type="password" name="password" placeholder="password" />
                <input type="submit" placeholder="password" value="Log In" />
                <!-- <h2>&nbsp;</h2>  -->
            </form>
        </div>
    </div>

    <script src="<?=PATH . ADMIN_TEMPLATE?>/resources/js/ajax_sitemap.js"></script>
    <script src="<?=PATH . ADMIN_TEMPLATE?>/resources/js/rune.js"></script>
    <script>
        
        let form = document.querySelector('form');

        if(form){

            form.addEventListener('submit', e => {

                if(e.isTrusted){

                    e.preventDefault();
                    
                    Ajax({

                        data: {
                            ajax: 'token'
                        }
                        
                    }).then(res => {

                        if(res){

                            res = res.trim();

                            form.insertAdjacentHTML('beforeend', `<input type="hidden" value="${res}" name="token">`)
                        }

                        form.submit();
                        
                    })
                    
                }
                
            })
                
}
        
    </script>
</body>
</html>