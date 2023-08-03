/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './styles/style.scss';

// Import libraries
const popper = require("@popperjs/core")
const bootstrap = require("bootstrap")

import './ckeditor/ckeditor';

// start the Stimulus application
import './bootstrap';


import "./gin/expandedDropdown";
import "./gin/addFancyTable";
import "./gin/smilesDrawer";
import "./gin/addTooltips";
import "./gin/formHelpers";
import "./gin/addClipboard";
//import "./gin/addSequenceViewer";
import "./gin/addQRReader";