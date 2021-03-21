const name      = document.getElementById("name");
const email     = document.getElementById("email");
const message   = document.getElementById("message");
const humanTest = document.getElementById("human_test");
const submit    = document.getElementById("submit");

let nameValid      = false;
let emailValid     = false;
let messageValid   = false;
let humanTestValid = false;

/**
 * Validates the email address.
 *
 * @param {string} email The email address
 *
 * @returns {boolean} If the email is valid or not
 */
const validEmail = (email) => {
	const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(String(email).toLowerCase());
}

/**
 * Check if all the inputs have at least something in the input
 * and set the appropriate disabled class on the submit button.
 */
const canSubmit = () => {
	if (nameValid && emailValid && messageValid && humanTestValid) {
		submit.classList.remove("disabled");
	} else {
		submit.classList.add("disabled");
	}
}

/**
 * Called when the submit button is clicked.
 */
submit.addEventListener("click", e => {
	if (submit.classList.contains("disabled")) {
		e.preventDefault();
	}
});

/**
 * Called when the name input keyup occurs
 * and will set if the name has something in the field.
 */
name.addEventListener("keyup", e => {
	nameValid = e.target.value.length > 0 ? true : false;
	canSubmit();
});

/**
 * Called when the email input keyup occurs
 * and will set if the name has something in the field.
 */
 email.addEventListener("keyup", e => {
	emailValid = validEmail(e.target.value);
	canSubmit();
});

/**
 * Called when the message input keyup occurs
 * and will set if the name has something in the field.
 */
 message.addEventListener("keyup", e => {
	messageValid = e.target.value.length > 0 ? true : false;
	canSubmit();
});

/**
 * Called when the humanTest input keyup occurs
 * and will set if the name has something in the field.
 */
 humanTest.addEventListener("keyup", e => {
	humanTestValid = e.target.value.length > 0 ? true : false;
	canSubmit();
});