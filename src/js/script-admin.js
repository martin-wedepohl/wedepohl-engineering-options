const selectIcon = document.querySelector( '.select-icon' );
const contentIcon = document.querySelector( '.content-icon' );

// Display selected dashicon for the Wedepohl Engineering Options
selectIcon.addEventListener( 'change', e => {
	const icon = e.target.value;
	contentIcon.classList = 'content-icon dashicons';
	contentIcon.classList.add(icon);
})