export function getImageUrl(path) {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }
    return `/storage/${path}`;
}

export function isFile(image) {
    return image instanceof File;
}

export function getWhatsAppLink(phoneNumber, message) {
    if (!phoneNumber) return null;

    if (phoneNumber.startsWith('08')) {
        phoneNumber = '62' + phoneNumber.slice(1);
    } else if (phoneNumber.startsWith('+')) {
        phoneNumber = phoneNumber.slice(1);
    }

    if (!/^\d+$/.test(phoneNumber)) {
        console.error("Invalid phone number format.");
        return null;
    }

    return `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
}

export function openWhatsAppChat(phoneNumber, message) {
    const url = getWhatsAppLink(phoneNumber, message);
    if (url) {
        window.open(url, "_blank");
    }
}