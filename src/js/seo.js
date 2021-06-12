const seoTitle = document.querySelector(".seo-title");
const seoSuffix = document.querySelector(".seo-suffix");
const seoDescription = document.querySelector(".seo-description");

const setTextPixelWidth = (text, title = true) => {
	
	const canvas = document.createElement("canvas");
	const ctx = canvas.getContext("2d");
	ctx.font = title ? "20px Arial" : "14px Arial";
	
	const seoSite = seo_ajax_object.site;
	const seoSeparator = seo_ajax_object.separator;

	if (title) {
		text += ` ${seoSeparator} ${seoSite}`;
		const len = text.length;
		const width = Math.round(ctx.measureText(text).width);
		const seoTitleHintChars = document.querySelector(".seo-title-hint .chars");
		const seoTitleHintPixels = document.querySelector(".seo-title-hint .pixels");
		const maxChars = 60;
		const maxPixels = 580;
		seoTitleHintChars.innerText = len;
		if (len > maxChars) {
			seoTitleHintChars.classList.add("error");
		} else {
			seoTitleHintChars.classList.remove("error");
		}
		seoTitleHintPixels.innerText = width;
		if (width > maxPixels) {
			seoTitleHintPixels.classList.add("error");
		} else {
			seoTitleHintPixels.classList.remove("error");
		}
	} else {
		const len = text.length;
		const width = Math.round(ctx.measureText(text).width);
		const seoDescriptionHintChars = document.querySelector(".seo-description-hint .chars");
		const seoDescriptionHintPixels = document.querySelector(".seo-description-hint .pixels");
		const seoMobileHint = document.querySelector(".seo-description-hint .mobile");
		const maxChars = 158;
		const maxPixels = 920;
		const maxMobileChars = 120;
		const maxMobilePixels = 680;
		seoDescriptionHintChars.innerText = len;
		if (len > maxChars) {
			seoDescriptionHintChars.classList.add("error");
		} else {
			seoDescriptionHintChars.classList.remove("error");
		}
		seoDescriptionHintPixels.innerText = width;
		if (width > maxPixels) {
			seoDescriptionHintPixels.classList.add("error");
		} else {
			seoDescriptionHintPixels.classList.remove("error");
		}
		if (len > maxMobileChars || width > maxMobilePixels) {
			seoMobileHint.classList.add("mobile-error");
		} else {
			seoMobileHint.classList.remove("mobile-error");
		}
	}

}

const title = seoTitle.value;
const text = seoDescription.value;
const seoSite = seo_ajax_object.site;
const seoSeparator = seo_ajax_object.separator;

seoSuffix.innerText = ` ${seoSeparator} ${seoSite}`;

setTextPixelWidth(title);
setTextPixelWidth(text, false);

seoTitle.addEventListener("keyup", (e) => {
	const title = e.target.value;
	setTextPixelWidth(title);
});

seoDescription.addEventListener("keyup", (e) => {
	const text = e.target.value;
	setTextPixelWidth(text, false);
});