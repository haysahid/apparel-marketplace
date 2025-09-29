const formatDate = (
    datetime: string,
    options: Intl.DateTimeFormatOptions = {
        dateStyle: "medium",
        timeStyle: "short",
    }
) => {
    if (!datetime) return null;

    options = {
        timeZone: "Asia/Jakarta",
        ...options,
    };

    return Intl.DateTimeFormat("id-ID", options).format(Date.parse(datetime));
};

export default formatDate;
