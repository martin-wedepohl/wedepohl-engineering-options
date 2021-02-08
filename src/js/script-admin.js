const selectIcon = document.querySelector( '.select-icon' );

if (null !== selectIcon) {
	// Display selected dashicon for the Wedepohl Engineering Options
	const contentIcon = document.querySelector( '.content-icon' );
	if (null !== contentIcon) {
		selectIcon.addEventListener( 'change', e => {
			const icon = e.target.value;
			contentIcon.classList = 'content-icon dashicons';
			contentIcon.classList.add(icon);
		});
	}
}