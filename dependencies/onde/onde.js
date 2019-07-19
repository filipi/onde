/*
 * Biblioteca de javascript
 * $Id: lib.js,v 1.7 2017/08/22 14:19:45 filipi Exp $
 */

function intval (mixed_var, base) {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: stensi
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   input by: Matteo
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: Rafał  Kukawski (http://kukawski.pl)
  // *     example 1: intval('Kevin van Zonneveld');
  // *     returns 1: 0
  // *     example 2: intval(4.2);
  // *     returns 2: 4
  // *     example 3: intval(42, 8);
  // *     returns 3: 42
  // *     example 4: intval('09');
  // *     returns 4: 9
  // *     example 5: intval('1e', 16);
  // *     returns 5: 30
  var tmp;
  var type = typeof(mixed_var);

  if (type === 'boolean') {
    return +mixed_var;
  } else if (type === 'string') {
    tmp = parseInt(mixed_var, base || 10);
    return (isNaN(tmp) || !isFinite(tmp)) ? 0 : tmp;
  } else if (type === 'number' && isFinite(mixed_var)) {
    return mixed_var | 0;
  } else {
    return 0;
  }
}

var isNN = (navigator.appName.indexOf('Netscape')!=-1);

function onlyNumbers(e){
  var keynum;
  var keychar;
  var numcheck;
  if(window.event){ // IE
    keynum = e.keyCode;
  }
  else
    if(e.which){ // Netscape/Firefox/Opera
      keynum = e.which;
    }
  if ((keynum < 48 || keynum > 57) && keynum!=8 && keynum!=46 ) return false;
}

function noNumbers(e){
  var keynum;
  var keychar;
  var numcheck;
  if(window.event){ // IE
    keynum = e.keyCode;
  }
  else
    if(e.which){ // Netscape/Firefox/Opera
      keynum = e.which;
    }
  keychar = String.fromCharCode(keynum);
  numcheck = /\d/;
  return !numcheck.test(keychar);
}

function abre(url){
  window.open(url,'','scrollbars=yes,width=650,height=450,top=0,left=0');
}

function autoTab(input,len, e){
  var keyCode = (isNN) ? e.which : e.keyCode;
  var filter = (isNN) ? [0,8,9] : [0,8,9,16,17,18,37,38,39,40,46];
  if(input.value.length >= len && !containsElement(filter,keyCode)){
    input.value = input.value.slice(0, len);
    input.form[(getIndex(input)+1) % input.form.length].focus();
  }
  function containsElement(arr, ele){
    var found = false, index = 0;
    while(!found && index < arr.length)
      if(arr[index] == ele)
        found = true;
      else
        index++;
      return found;
  }
  function getIndex(input){
    var index = -1, i = 0, found = false;
    while (i < input.form.length && index == -1)
      if (input.form[i] == input)
        index = i;
      else
        i++;
      return index;
  }
  return true;
}

function checkEmail(myForm){
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(myForm.email.value))
  {
    return (true)
  }
  alert("EndereÃ§o de E-mail invÃ¡lido. Por favor, tente novamente.")
  return (false)
}

// Nannette Thacker http://www.shiningstar.net
function confirmSubmit(){
  var agree=confirm("Tem certeza?");
  if (agree)
    return true ;
  else
    return false ;
}

//https://stackoverflow.com/questions/916528/how-can-i-hide-a-td-tag-using-inline-javascript-or-css
function toggleClass(className){
  if (className == null){
  }
  else{
    var elements = document.getElementsByClassName(className);    
    for (var i = 0, length = elements.length; i < length; i++){
      if (elements[i].style.display == 'block' || elements[i].style.display == 'inline-block')
	elements[i].style.display = 'none';
      else{
	if (elements[i].tagName == 'DIV'){
          elements[i].style.display = 'inline-block';
          //elements[i].style.background_color = 'red';	  
          //elements[i].style.width = '100 px';
	  //console.log('id: ' + elements[i].id);
	  //console.log('passei i:' + i);
	}
	else
          elements[i].style.display = 'block';
      }
    }
  }
}

function toggleShowHide(id1, id2){
  var field1 = document.getElementById(id1);
  var field2 = document.getElementById(id2);
  if ((field1 == null) && (field2 == null)){
  }
  else if (field1.style.display == "block"){
    field1.style.display = "none";
    field2.style.display = "block";
  }
  else{
    field1.style.display = "block";
    field2.style.display = "none";
  }
}

function toggleField(id1, id2, escondido){
  var field1 = document.getElementById(id1);
  var field2 = document.getElementById(id2);
  if ((field1 == null) && (field2 == null)){
  }
  else if (field1.style.display == "block"){
    field1.style.display = "none";
    field2.style.display = "block";
    escondido.value = "cadastro";
  }
  else{
    field1.style.display = "block";
    field2.style.display = "none";
    escondido.value = "indice";
  }
}

function trimAll(sString){
  while (sString.substring(0,1) == ' '){
    sString = sString.substring(1, sString.length);
  }
  while (sString.substring(sString.length-1, sString.length) == ' '){
    sString = sString.substring(0,sString.length-1);
  }
  return sString;
}

function verifica(c,f) {    // STRING DOS CAMPOS , FORMULÁRIO
  lista = c.split(",");
  f = document.forms[f];
  erro = 0;
  for (i = 0; i < lista.length; i++) {
    lt = lista[i];
    if (f[lt].value.length <= 0) {
      alert("Observe o preenchimento dos campos obrigatórios.");
      erro = 1;
      break;
    }
  }
  if (erro == 0) f.submit();
}

function verificaEqp(c,f) {    // STRING DOS CAMPOS , FORMULÁRIO
  lista = c.split(",");
  f = document.forms[f];
  erro = 0;
  for (i = 0; i < lista.length; i++) {
    lt = lista[i];
    if (f[lt].value.length <= 0) {
      alert("Você deve selecionar sua Equipe e digitar a senha cadastrada.");
      erro = 1;
      break;
    }
  }
  if (erro == 0) f.submit();
}
