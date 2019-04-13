/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
//require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');
import $ from 'jquery';
global.$ = global.jQuery = $;
console.log('bootstrap-italia');


//import "bootstrap-italia";
//import "bootstrap-italia/dist/css/bootstrap-italia.min.css";
//import "bootstrap-select";
//import "bootstrap";
//import "owl.carousel";
//import "popper.js";
//import "svgxuse";
//import "bootstrap-confirmation2";
//import "font-awesome/css/font-awesome.min.css";

window.bootstrap = require("bootstrap-italia");
require("bootstrap-italia/dist/css/bootstrap-italia.min.css");
require("bootstrap-select");
require("bootstrap");
require("owl.carousel");
global.Popper = require('popper.js');
require("svgxuse");
require("bootstrap-confirmation2");
require("font-awesome/css/font-awesome.min.css");

global.bootbox = require('bootbox');

require("./spinner/waitpage.js");
require("./notification/notification.js");


