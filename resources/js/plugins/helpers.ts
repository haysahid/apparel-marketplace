export function getImageUrl(path: string) {
    if (!path) return null;
    if (path.startsWith("http://") || path.startsWith("https://")) {
        return path;
    }
    return `/storage/${path}`;
}

export function isFile(image: any) {
    return image instanceof File;
}

export function getWhatsAppLink(phoneNumber: string, message: string = null) {
    if (!phoneNumber) return null;

    // Replace non-digit characters except '+' sign
    phoneNumber = phoneNumber.replace(/[^\d+]/g, "");

    if (phoneNumber.startsWith("08")) {
        phoneNumber = "62" + phoneNumber.slice(1);
    } else if (phoneNumber.startsWith("+")) {
        phoneNumber = phoneNumber.slice(1);
    }

    phoneNumber = phoneNumber.replace(/[\s-]/g, "");

    if (!/^\d+$/.test(phoneNumber)) {
        console.error("Invalid phone number format.");
        return null;
    }

    const url = `https://wa.me/${phoneNumber}`;

    if (message != null) {
        return `${url}?text=${encodeURIComponent(message)}`;
    }

    return url;
}

export function openWhatsAppChat(phoneNumber: any, message: any) {
    const url = getWhatsAppLink(phoneNumber, message);
    if (url) {
        window.open(url, "_blank");
    }
}

export function scrollToTop({ id = null }) {
    if (id) {
        const element = document.getElementById(id);
        if (element) {
            element.scrollTo({ top: 0, behavior: "smooth" });
            return;
        }
    }

    window.scrollTo({ top: 0, behavior: "smooth" });
}

export function goBack() {
    window.history.back();
}
