var FCBData = {};

$(function(){

	_('botao').style.display = 'block';

	_('userLink').onclick = function(){
		if(_('userName').innerHTML == 'Login') {
			resolveLogin();
		}
		return false;		
	}

	//Testa e carrega o login
	//setTimeout(resolveLogin(false), 60000);
	

})

function setUser(){
	//Enviando dados ao servidor
	$.ajax(_URL+'x/userlog',{method:"POST", data:FCBData})
	 .done(function(e){
	 	_('userImage').src = e.picture;
		_('userLink').title = e.name;
		_('userLink').href = _URL+'u/'+e.id;
	 });
}

function resolveLogin(login){

	FB.getLoginStatus(function(response) {

		if (response.status === 'connected') {
			FCBData.id = response.authResponse.userID;
			FCBData.token = response.authResponse.accessToken;
			FCBData.expire = response.authResponse.expiresIn;

			setUser();
		} else {
			if(true === login) {	fbLogin() }
		} 
	});
}

function fbLogin(){
	FB.login(function(r){
		if (r.authResponse) {
			FB.api('/me', 
				{fields: 'id,name,link,gender,locale,picture,timezone,email'}, 
				function(d) {

					FCBData.name = d.name;
					FCBData.locale = d.locale;
					FCBData.id = d.id;
					FCBData.email = d.email;
					FCBData.gender = d.gender;
					FCBData.link = d.link;
					FCBData.timezone = d.timezone;
					FCBData.picture = d.picture.data.url;

					var e = FB.getAuthResponse();
					FCBData.token = e.accessToken;
					FCBData.expire = e.expiresIn;

					setUser();
				}
			);
		} else {
			alert("Não foi possível logar no FACEBOOK!\nVou te redirecionar para a página de login.");
			window.location.href = _URL+'login';
		}
	});
}


function _(e){return document.getElementById(e);}


//FACEBOOK --------------------------------------
window.fbAsyncInit = function() {
	FB.init({
		appId      : '1074509609321655',
			xfbml      : true,
			version    : 'v2.8'
	});
	FB.AppEvents.logPageView();
	resolveLogin(false)
};

(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/pt_BR/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));