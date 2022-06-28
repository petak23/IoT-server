import jquery from 'jquery';
import 'bootstrap'; //from 'bootstrap'

import naja from 'naja';
document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));

import netteForms from 'nette-forms';
netteForms.initOnLoad(); 
window.Nette = netteForms;

import './scripts.js';

import '../css/main.css';