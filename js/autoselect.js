	/* Начин на употреба : onKeyPress="aoutoSelect(this);" */

		var str = '';
		var moz = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined');
		
		function autoSelect(s) 
		{				
			/* проверка на бараузъра */
			if (moz)
			{
				var key = event.charCode;
			}
			else
			{
				var key = event.keyCode;
			}			

			var hint = document.getElementById('hint');			
			
			/* създаваме hint-a */
			if (hint == null)
			{		
				var hint = document.createElement('SPAN');
				/* задаване id на hint-а */
				hint.id = 'hint';
				/* задаване стил на hint-a */
				hint.style.background = '#FBF8B3';
				hint.style.border = '1px solid black';
				hint.style.textAlign = 'center';
				hint.style.display = '';
				/* задаване координати на hint-а */
				hint.style.position = 'absolute';
				hint.style.top = s.offsetTop - 23;
				hint.style.left = s.offsetLeft;
				hint.style.zIndex = 1000;
				hint.innerHTML = '';
				document.body.appendChild(hint);				
			}
			else
			{
				/* проверка дали не е сменен обекта	*/
				if (hint.style.top != (s.offsetTop - 23)+ 'px')
					str = '';
				/* коригиране координатите на hint-a */
				hint.style.top = s.offsetTop - 23;
				hint.style.left = s.offsetLeft;
			}						
			
			var found = false;
			/* изтриване ако се натисне ESC */
			if (key == 27)
				str = '';
			else
				str += String.fromCharCode(key);
			
			for (i = 0; i < s.length; i++)
			{		
				var main = s[i].text;
				offset = main.toLowerCase().indexOf(str.toLowerCase());
				if (offset == 0)
				{
					found = true;
					break;
				}
			}
			
			/* избира елемент */
			if (found) { s.selectedIndex = i; }			
			/* ако не е намерен избира първия и нулира hint-a */
			else if (key > 0) { s.selectedIndex = 0; str = ''; }
			hint.innerHTML = str;
			hint.style.display = '';			
			hint.style.width = (hint.innerHTML.length * 11);
			
			if (hint.innerHTML.length == 1) hint.style.width = 13;
			/* показване за 777 милисекунди */
			window.setTimeout("hint.style.display = 'none';",777);
		}