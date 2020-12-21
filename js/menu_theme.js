
// directory of where all the images are
var cmThemeOfficeBase = './images/';

var cmThemeOffice =
{
  	// main menu display attributes
  	//
  	// Note.  When the menu bar is horizontal,
  	// mainFolderLeft and mainFolderRight are
  	// put in <span></span>.  When the menu
  	// bar is vertical, they would be put in
  	// a separate TD cell.

  	// HTML code to the left of the folder item
  	mainFolderLeft: '&nbsp;&nbsp;&nbsp;',
  	// HTML code to the right of the folder item
  	mainFolderRight: '&nbsp;&nbsp;&nbsp;',
	// HTML code to the left of the regular item
	mainItemLeft: '&nbsp;&nbsp;&nbsp;',
	// HTML code to the right of the regular item
	mainItemRight: '&nbsp;&nbsp;&nbsp;',

	// sub menu display attributes

	// 0, HTML code to the left of the folder item
	folderLeft: '<i class="fa fa-folder-open-o">',
	// 1, HTML code to the right of the folder item
	folderRight: '<i class="fa fa-angle-right"> ',
	// 2, HTML code to the left of the regular item
	itemLeft: '<i class="fa fa-file-o">',
	// 3, HTML code to the right of the regular item
	itemRight: '',
	// 4, cell spacing for main menu
	itemSeparator: '&nbsp;:&nbsp;',
	
	mainSpacing: 0,
	// 5, cell spacing for sub menus
	subSpacing: 4,
	// 6, auto dispear time for submenus in milli-seconds
	delay: 1000
};

// for horizontal menu split
var cmThemeOfficeHSplit = [_cmNoAction, '<td class="ThemeOfficeMenuItemLeft"></td><td colspan="2"><div class="ThemeOfficeMenuSplit"></div>'];
var cmThemeOfficeMainHSplit = [_cmNoAction, '<td colspan="2"><div class="ThemeOfficeMenuSplit"></div>'];
var cmThemeOfficeMainVSplit = [_cmNoAction, ''];

