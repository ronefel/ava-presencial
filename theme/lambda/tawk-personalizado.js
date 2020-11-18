/**
 * classe que detecta o dispositivo que o usuário está acessando
 */
const md = new MobileDetect(window.navigator.userAgent);

/**
 * informa se o dispositivo que o usuário está acessando é móvel
 */
let isMobile = false;
if(md.mobile() != null || md.mobile() == 'UnknownPhone'){
    isMobile = true;
}

/**
 * Oculta o chat após o seu carregamento e mostra o botão personalizado
 */
Tawk_API.onLoad = () => {
    if(isMobile){
        Tawk_API.toggleVisibility();
        document.getElementById('tawkbutton').style.display = 'block';
    }

    // Mostra o botão do whatsapp junto com o botão do chat
    document.getElementById('whatsappbutton').style.display = 'block';
};

/**
 * Muda o ícone do botão personalizado de acordo com o status
 */
Tawk_API.onStatusChange = (status) => {
    if(status === 'online') { // quando online
        document.getElementById('tawkbutton-toggle').innerHTML = '<i class=\"fa fa-comments-o\" aria-hidden=\"true\"></i>';
    }
    else if(status === 'away') { // quando ausente
        document.getElementById('tawkbutton-toggle').innerHTML = '<i class=\"fa fa-comments-o\" aria-hidden=\"true\"></i>';
    }
    else if(status === 'offline') { // quando offline
        document.getElementById('tawkbutton-toggle').innerHTML = '<i class=\"fa fa-envelope-o\" aria-hidden=\"true\"></i>';
    }
    // nessesário para ocultar o chat quando muda de status
    // isChatHidden() e hideWidget() só funcionam direito dentro do setTimeout
    if(isMobile){
        setTimeout(() => {
            if(!Tawk_API.isChatHidden()){
                Tawk_API.hideWidget();
            }
        }, 0);
    }
};

/**
 * Se o chat está minimizado, oculta o chat.
 * Esconde o botão original quando o chat é minimizado
 */
Tawk_API.onChatMinimized = () => {
    if(isMobile){
        Tawk_API.toggleVisibility();
    }
};

/**
 * Se o chat está maximizado, desoculta o chat.
 * onChatHidden sempre é chamado quando é maximizado ou minimizado o chat
 */
Tawk_API.onChatHidden = () => {
    if(isMobile){
        if(Tawk_API.isChatMaximized()){
            Tawk_API.toggleVisibility();
        }
    }
};

/**
 * =============- onChatMaximized não funciona em dispositivos móveis -===============
 */
// Tawk_API.onChatMaximized = () => {
//     if(isMobile){
//         Tawk_API.toggleVisibility();
//     }
// };

/**
 * Renderiza o botão personalizado no final do body 
 * e o botão do whatsapp
 */
document.body.innerHTML += `<div id='whatsappbutton'>
<a href='https://api.whatsapp.com/send?phone=556999851010' target='_blank' title='Tutoria Administrativa'>
    <div id='whatsappbutton-toggle'>
        <i class='fa fa-whatsapp' aria-hidden='true'></i>
    </div>
</a>
</div>
<div id='tawkbutton'>
<a href='javascript:void(Tawk_API.toggle())'>
    <div id='tawkbutton-toggle'>
        <i class='fa fa-comments-o' aria-hidden='true'></i>
    </div>
</a>
</div>

<style>
#tawkbutton {
    border: 0px none !important;
    padding: 0px !important;
    z-index: 999999999 !important;
    overflow: visible !important;
    min-width: 33px !important;
    min-height: 33px !important;
    max-width: 33px !important;
    max-height: 33px !important;
    width: 33px !important;
    height: 33px !important;
    position: fixed !important;
    display: none;
    margin: 0px !important;
    bottom: 3px !important;
    top: auto !important;
    right: 3px !important;
    left: auto !important;
}
#tawkbutton a {
	color: #fff;
    margin: 0px;
    padding: 0;
    line-height: 29px;
}
#tawkbutton-toggle {
	padding: 0;
    position: absolute;
    z-index: 999998;
    width: 100%;
    height: 100%;
    border: 0 none;
    text-align: center;
    font-size: 20px;
    background-color: #bf2b0e;
    border-radius: 50%;
}
#whatsappbutton {
	border: 0px none;
    padding: 0px;
    z-index: 999999999;
    overflow: visible;
    width: 60px;
    height: 60px;
    position: fixed;
    display: none;
    margin: 0px;
    bottom: 20px;
    top: auto;
    right: 90px;
    left: auto;
}
#whatsappbutton a {
	color: #fff;
    margin: 0px;
    padding: 0;
    line-height: 58px;
}
#whatsappbutton-toggle {
    padding: 0;
    position: absolute;
    z-index: 999998;
    width: 100%;
    height: 100%;
    border: 0 none;
    text-align: center;
    font-size: 40px;
    background-color: #4ecb5d;
    border-radius: 50%;
}
.back-to-top {
    display: none !important;
}
</style>`;

// Se dispositivo móvel, diminui o tamanho do botão do whatsapp
if(isMobile){
    document.body.innerHTML += `<style>
#whatsappbutton {
    width: 33px;
    height: 33px;
    bottom: 39px;
    right: 3px;
}
#whatsappbutton a {
    line-height: 30px;
}
#whatsappbutton-toggle{
    font-size: 22px;
}
</style>`;
};