/* 
 * script.js app file
 * Posledná zmena(last change): 20.04.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 */

/*** Sidebar toogle ***/
document.getElementById("sidebarToggle").addEventListener("click", sidebarClick, false);

function sidebarClick(event) {
  event.preventDefault();
  document.getElementsByTagName("body")[0].classList.toggle("sb-sidenav-toggled")
}
/*** End of Sidebar toogle ***/