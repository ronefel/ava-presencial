//Carrego o primeiro slide e chamo a função de verificar botão para
// deixar o botão de retorno invisível no primeiro slide.

function toggleMenu() {
	$("#showmenu").trigger( "click" );
}

// Next/previous controls
function plusSlides(n) {
  showSlides(slideIndex += n);
}

// Thumbnail image controls
function currentSlide(n) {
  showSlides(slideIndex = n);
}

var slideIndex = 1;

function showSlides(n) {
  var i;
  var slides = document.getElementsByClassName("mySlides");
  var dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
} 

$(document).ready(function(){
	
	$('#showmenu').click(function() {
        $('.menu-cenas-footer').slideToggle("fast");
        $('.overlay').fadeToggle(400);
    });

	$('.overlay').click(function() {
        $("#showmenu").trigger( "click" );
    });

	msieversion();
	if (msieversion() < 9 && msieversion() >0){
		alert("Seu navegador não é adequado para visualizar este curso, " + 
			"recomendamos que utilize o Google Chrome para navegação, " + 
			"ou atualize seu navegador." );
	}
	if (getUrlVars()["pag"] != null) {
	var pag = getUrlVars()["pag"];
	vai( parseInt(pag));
	verificaBotao();
	}else{	
	$('#slide0').empty().load('slides/0.html');
	$('#loader').fadeOut(50);
	$('#slide0').css({opacity: '1'});
	}
	$('#sl-tot').append($('.palco').length);
	if (getUrlVars()["pag"] == null) {
		if (readCookie(nomeCurso)){
			if (confirm('Deseja continuar de onde parou em seu último acesso?')){
				vai(parseInt(readCookie(nomeCurso)));
			}
		}
		verificaBotao();
	}


	$('.menu-cenas-footer').hide();
	$('.overlay').hide();

});

if (getUrlVars()["indice"] == null) {
	var varFechar = -1	
} else {
	var varFechar = getUrlVars()["indice"];
}

var nomeCurso = $nome;
//Declaro algumas variáveis, também chamo a função pressionaTecla
// toda vez que uma tecla é pressionada.
var urlId = '1';
var slideAtual = "0";
var proximoSlide = "1"
var travaTeclado=false;
var carBloqTeclado=false;
var	slideAnterior;
var slideMenu = 2;
var menuAtivo = false;
window.onkeydown = pressionaTecla;

function vaiMod(mod, pag){
	varFechar--;		
	if (pag>0) {
		window.location = "../modulo" + mod.toString() +
		"/index.html?pag=" + pag + "&indice=" + varFechar;
	}
	else {
		window.location ="../modulo"+mod.toString() + "/index.html"
	}
}

function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(
    	window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}		
		
//função para centralizar a div wrapper em qualquer dimensão
function sizeBox() {
	var w = $(window).width();
	var h = $(window).height();
}

$(window).load(function() {
	sizeBox();
});
$(window).resize(function() {
	sizeBox();
});
		
/** COOKIES **/
function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
function eraseCookie(name) {
	createCookie(name,"",-1);
}		
		
/****
A função pressionaTecla verifica se a travaTeclado está ativa
para evitar excesso de contagem de slides e transição sobre
transição que pode ocasionar bugs, depois verifica qual a tecla
pressionada para tratar a transição de slides, caso esteja disponível.
****/

function pressionaTecla(e)
{if (travaTeclado==false){
	if ((e.keyCode==39 || e.keyCode==40) && $('.palco').length > slideAtual+1){
		avancaSlide();
		travaTeclado=true;
	} 
	else if ((e.keyCode==37 || e.keyCode==38) && slideAtual>0){
		voltaSlide();
		travaTeclado=true;
	} 
}}
		
//A função controlador é semelhante a função pressionaTecla,
// mas funciona com os links dos botões controladores.
function controlador(posicao){
	if (travaTeclado==false){
	if (slideAtual==0) {
		$('#capa').animate({marginTop:'100%'},1000); 
	}
	if (posicao==1 && $('.palco').length > slideAtual+1){
		avancaSlide();
		travaTeclado=true;
	} 
	else if (posicao==2&& slideAtual>0){
			voltaSlide();
			travaTeclado=true;
		} 
	}
}
	
function fechaModulo(){
	window.history.go(varFechar);
}

function menu(){

	$('#menu').empty().load('../menu/menu.html');
	$('#menu').css({zIndex:'4500'});
	$('#menu').fadeOut(0);
	$('#menu').fadeIn(800);
	$('.menu').css({zIndex:'999999'});

	slideMenu = slideAtual;
	
	urlId = ('menu').toString();
	$('#slide'+slideAtual).fadeOut(800);
	$('#menu').css({opacity: '1'});
	$('#menu').empty();

	setTimeout(function(){
		for (var i=0; i<= $('.palco').length; i++){
			$('#slide'+i).fadeOut(0);
		}
	
		$('#slide'+slideAtual).empty();
		$('#menu').fadeOut(0);
		$('#menu').fadeIn(800);
		$('#menu').empty().load('slides/'+urlId+'.html');
		
		//Atualizo as variáveis e verifico se o botão deve ficar visível,
		// também libero o teclado e os controladores para a próxima transição.
		slideAnterior = slideAtual-1;
		slideAtual = slideAtual;
		proximoSlide =slideAtual+1;
		verificaBotao();
		
		$('.infoslide').css({zIndex:'-1'});
		travaTeclado = false;
		urlId = (proximoSlide).toString();
	},800);
	window.scrollTo(0, 0);
}

$('#menu').css({zIndex:'-10'});
function fechaMenu(){

	$('#menu-sanfona').animate({marginTop:'100%'},400); 
	$('#menu').fadeOut(500);
	$('#menu').css({zIndex:'-10'});
	$('#menu').delay(500).css({zIndex:'0'});
	$('.menu').css({zIndex:'1000'});

	urlId = (slideAtual).toString();
	$('#menu').fadeOut(800);
	$('#slide'+slideAtual).css({opacity: '1'});
	$('#slide'+slideAtual).empty();
	setTimeout(function(){
		for (var i=0; i<= $('.palco').length; i++){
			$('#slide'+i).fadeOut(0);
		}
		$('#menu').empty();
		$('#slide'+slideAtual).fadeOut(0);
		$('#slide'+slideAtual).fadeIn(800);
		$('#slide'+slideAtual).empty().load('slides/'+urlId+'.html');
		
		//Atualizo as variáveis e verifico se o botão deve ficar visível,
		// também libero o teclado e os controladores para a próxima transição.
		
		urlId = (proximoSlide).toString();
		$('.infoslide').css({zIndex:'1'});
	},800);
	window.scrollTo(0, 0);
}

function vai(novoSlide){
	
	$("#showmenu").trigger( "click" );

	if (novoSlide != slideAtual){
	if (menuAtivo == true) {menuAtivo = false; vai(novoSlide); $('#menu').empty();}
	else{
		urlId = (novoSlide).toString();
		$('#slide'+slideAtual).fadeOut(800);
		$('#slide'+novoSlide).css({opacity: '1'});
		$('#slide'+novoSlide).empty();
		$('#loader').fadeIn(50);
		setTimeout(function(){
		for (var i=0; i<= $('.palco').length; i++){
		$('#slide'+i).fadeOut(0);
		}
		$('#slide'+slideAtual).empty();
		$('#slide'+novoSlide).fadeOut(0);
		$('#slide'+novoSlide).fadeIn(800);
		$('#slide'+novoSlide).empty();
		$('#loader').fadeOut(50);
		$('#slide'+novoSlide).load('slides/'+urlId+'.html');
		
		//Atualizo as variáveis e verifico se o botão deve ficar visível, também libero o teclado e os controladores para a próxima transição.
		slideAnterior = novoSlide-1;
		slideAtual= novoSlide;
		proximoSlide=novoSlide+1;
		 verificaBotao()
		travaTeclado = false;
		urlId = (proximoSlide).toString();
		$('.infoslide').css({zIndex:'1'});
		},800);
		window.scrollTo(0, 0);
		}}
	}

//Esta função é chamada para fazer a transição de avanço.
// Ela faz o slide que está na tela desaparecer em direção a esquerda,
// então carrega o próximo slide e faz ele aparecer vindo da direção direita.
function avancaSlide(){
	if (menuAtivo == true) {fechaMenu();}else{
		$('#loader').fadeIn(50);
		$('#slide'+slideAtual).toggle( "slide", {direction: "left"}, 400); 
		$('#slide'+proximoSlide).empty();
		setTimeout(function(){
		$('#slide'+slideAtual).empty();
		$('#slide'+proximoSlide).css({opacity: '1'});
		$('#slide'+proximoSlide).css({display:'none'});
		$('#slide'+proximoSlide).toggle( "slide", {direction: "right"}, 400); 
		$('#slide'+proximoSlide).empty();
		$('#loader').fadeOut(50);
		$('#slide'+proximoSlide).load('slides/'+urlId+'.html');
		//Atualizo as variáveis e verifico se o botão deve ficar visível, 
		//também libero o teclado e os controladores para a próxima transição.
		slideAnterior = slideAtual;
		slideAtual++;
		proximoSlide++;
		var aux = parseInt(urlId);
		aux +=1;
		urlId = aux.toString();
		travaTeclado=false;
		verificaBotao();
		},400);
		window.scrollTo(0, 0);
	}
}

//Semelhante a função que avança, mas esta faz o caminho contrário.
function voltaSlide(){
	if (menuAtivo == true) {
		fechaMenu();
	}
	else {
		var aux = parseInt(urlId);
		aux --;
		urlId = aux.toString();
		$('#loader').fadeIn(50);
		$('#slide'+slideAtual).toggle("slide", {direction: "right"}, 400); 
		setTimeout(function(){
			$('#slide'+slideAtual).empty();
			$('#slide'+slideAnterior).css({opacity: '1'});
			$('#slide'+slideAnterior).fadeOut(0);
			$('#slide'+slideAnterior).toggle( "slide", {direction: "left"}, 400); 
			$('#slide'+slideAnterior).empty();
			$('#loader').fadeOut(50);
			$('#slide'+slideAnterior).load('slides/'+(urlId-1)+'.html');
			slideAnterior--;
			slideAtual--;
			proximoSlide--;
			travaTeclado=false;
			 verificaBotao()
		},400);
		window.scrollTo(0, 0);
	}
}

function verificaBotao(){
	if (proximoSlide==($('.palco').length)) {
		$('.footProx').css({opacity:'0'});
	} else {
		$('.footProx').css({opacity:'1'});
	}
	if (proximoSlide==(1)){
		$('.footPrev').css({opacity:'0'});
		//$('.titulo').css({display:'none'});
		$('.menu').css({zIndex:'-1'});
	} else {	
		$('.menu').css({zIndex:'1000'});
		$('.footPrev').css({opacity:'1'});
		/*$('.titulo').fadeIn(700);
		$('.titulo p').delay(500).fadeIn(800);*/
	}
	$('#sl-at').empty();
	$('#sl-at').append(parseInt(slideAtual)+1);
	eraseCookie(nomeCurso);
	createCookie(nomeCurso,slideAtual,100);
}

function tooltip(){ 
	$('.botao-map span').css({opacity :'0'});
	$('.botao-map span').css({display :'none'});
	$('a.botao-map').hover(function(){
	$(this).addClass('foco');
	//$(this).css({background:'#6D7B81'});
	$(this).addClass('checked');
	$('.botao-map:not(.foco)').fadeOut(400);
	$(this).fadeIn(0);
	$(this).find('span').fadeIn(200);
	$(this).find('span').animate(
		{top:'10px', opacity: 1, display :'block'},
		{queue:false,duration:200});
	}, function(){
		$('.botao-map').fadeIn(400);
		$(this).find('span').animate(
			{top:'0px',opacity: 0, display :'none'},
			{queue:false,duration:200});
		$(this).find('span').fadeOut(100);	
		$(this).removeClass('foco');
	});
}
//Script para desativar scroll com setas no firefox
// (buga a navegação em baixa resolução se não tiver esse script)
//<![CDATA[
window.onload = function(){
	var temp = document.createElement('p');
	/*for(var i = 0; i < 100; i++){
	document.body.appendChild( temp.cloneNode(true)  );
	}*/
	addEvent(document.body, 'keydown', keyDown);
	addEvent(window, 'keydown', keyDown);
}
function addEvent(obj, evType, fn) {
	if(obj.addEventListener){
		obj.addEventListener(evType, fn, false);
		return true;
	} else if(obj.attachEvent){
		var r = obj.attachEvent("on"+evType, fn);
		return r;
	 }
}
function keyDown(e) {
	var ev = e||event;
	var key = ev.which||ev.keyCode;
	var esc = 0;

	switch(key){
		case 37: // left
		case 38: // up
		case 39: // right
		case 40: // down
		esc = 1;
		break;
	}
	if(esc && ev.preventDefault){
		ev.preventDefault();
	}
	return esc;
}
	
// ]]>
//Fim do Script// JavaScript Document
function icones(){ 	
	$('.efeito_fade').append('<span class="hover"></span>').each(function () {
	var $span = $('> span.hover', this).css('opacity', 0);
	$(this).hover(function () {
	$span.stop().fadeTo(500, 1);
		}, function () {
				$span.stop().fadeTo(500, 0);
			});
	});
}
function msieversion(){
  var ua = window.navigator.userAgent
  var msie = ua.indexOf ( "MSIE " )

  if ( msie > 0 )      // If Internet Explorer, return version number
     return parseInt (ua.substring (msie+5, ua.indexOf (".", msie )))
  else                 // If another browser, return 0
     return 0
}
   