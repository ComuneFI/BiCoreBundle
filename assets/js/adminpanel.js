/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
import '../css/adminpanel/pannelloamministrazione.css';
//import "bootstrap-italia";
//import "bootstrap-italia/dist/css/bootstrap-italia.min.css";

//import $ from 'jquery';
//global.$ = global.jQuery = $;

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');
import "./adminpanel/pannelloamministrazione.js";
import "./adminpanel/pannelloamministrazionecommands.js";
import "./adminpanel/pannelloamministrazionefunzioni.js";
 
console.log('Hello Webpack Encore! Edit me in assets/js/login.js');
