// SET DEFAULT VALUE FOR THEME MODE
//contentLayout, navbar, footer, theme, menuLayout
if (localStorage.getItem('theme') == null) {
  localStorage.setItem('theme', __DEFAULT_THEME)
}
if (localStorage.getItem('contentLayout') == null || localStorage.getItem('layout_migrated_v2') == null) {
  localStorage.setItem('contentLayout', 'layout-full')
  localStorage.setItem('layout_migrated_v2', 'true')
}
if (localStorage.getItem('navbar') == null || localStorage.getItem('navbar_migrated_v2') == null) {
  localStorage.setItem('navbar', 'sticky')
  localStorage.setItem('navbar_migrated_v2', 'true')
}
if (localStorage.getItem('footer') == null) {
  localStorage.setItem('footer', 'static')
}
if (localStorage.getItem('menuLayout') == null) {
  localStorage.setItem('menuLayout', 'horizontalMenu')
}

/*==================================================
GET DARK-LIGHT-OR-SEMI-DARK VALUE FROM LOCAL STORAGE
===================================================*/
const rootEl = document.documentElement
const themeMode = localStorage.getItem('theme') || 'light'
const sidebar = document.querySelector('.sidebar-wrapper')

if (themeMode === 'dark') {
  rootEl.classList.add('dark')
  rootEl.classList.remove('light')
  rootEl.classList.remove('semiDark')
} else if (themeMode === 'semiDark') {
  rootEl.classList.add('semiDark')
  rootEl.classList.add('light')
  rootEl.classList.remove('dark')
} else if (themeMode === 'auto') {
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    rootEl.classList.add('dark')
    rootEl.classList.remove('light')
  } else {
    rootEl.classList.add('light')
    rootEl.classList.remove('dark')
  }
} else if (themeMode === 'default') {
  rootEl.classList.remove('dark')
  rootEl.classList.remove('light')
  rootEl.classList.remove('semiDark')
} else {
  rootEl.classList.add(themeMode)
  rootEl.classList.remove('dark')
  rootEl.classList.remove('semiDark')
}

/*=======================================
  GET GRAY-SCALE VALUE FROM LOCAL STORAGE
=======================================*/
// Check if the value of the "effect" key in the local storage is "grayScale"
if (localStorage.effect === 'grayScale') {
  // If it is, add the "grayScale" class to the "html" element
  rootEl.classList.add('grayScale')
} else {
  // If it's not, remove the "grayScale" class from the "html" element
  rootEl.classList.remove('grayScale')
}

/*=======================================
  GET RTL/LTR VALUE FROM LOCAL STORAGE
=======================================*/
// Check if the value of the "effect" key in the local storage is "grayScale"
if (localStorage.dir === 'rtl') {
  // If it is, add the "grayScale" class to the "html" element
  rootEl.dir = 'rtl'
} else {
  // If it's not, remove the "grayScale" class from the "html" element
  rootEl.dir = 'ltr'
}

/*==========================================================
  GET Vertical and horizontal menu layout FROM LOCAL STORAGE
==========================================================*/
if (localStorage.menuLayout == 'horizontalMenu') {
  rootEl.classList.add('horizontalMenu')
} else {
  rootEl.classList.remove('horizontalMenu')
}

/*===================================
  GET layout type FROM LOCAL STORAGE
=====================================*/
rootEl.classList.add('layout-full')
rootEl.classList.remove('layout-boxed')
rootEl.classList.add('nav-sticky')
rootEl.classList.remove('nav-floating')
rootEl.classList.remove('nav-hidden')
rootEl.classList.remove('nav-static')

// Sync LocalStorage
localStorage.setItem('contentLayout', 'layout-full')
localStorage.setItem('navbar', 'sticky')
