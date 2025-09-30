import axios from "axios";
import cookieManager from "./cookie-manager";

async function initScript() {
    const snapScript = "https://app.sandbox.midtrans.com/snap/snap.js";
    const clientKey = import.meta.env.VITE_MIDTRANS_CLIENT_KEY;

    const script = document.createElement("script");
    script.src = snapScript;
    script.setAttribute("data-client-key", clientKey);
    script.async = true;

    document.body.appendChild(script);

    return () => {
        document.body.removeChild(script);
    };
}

async function checkPayment(
    { transactionCode, isGuest },
    {
        onSuccess = (response) => {},
        onError = (error) => {},
        onChangeStatus = (status) => {},
    }
) {
    onChangeStatus("loading");

    await axios
        .get(
            isGuest
                ? `/api/check-payment-guest?transaction_code=${transactionCode}`
                : `/api/check-payment?transaction_code=${transactionCode}`,
            {
                headers: {
                    authorization: `Bearer ${cookieManager.getItem(
                        "access_token"
                    )}`,
                },
            }
        )
        .then((response) => {
            onChangeStatus("success");
            onSuccess(response);
        })
        .catch((error) => {
            onChangeStatus("error");
            onError(error);
        });
}

async function showSnap(
    { snapToken, delay = 1000 },
    {
        onSuccess = (result) => {},
        onPending = (result) => {},
        onError = (result) => {},
        onClose = () => {},
        onChangeStatus = (status) => {},
    }
) {
    console.log("showSnap called");
    onChangeStatus("loading");

    if (!window.snap) {
        await initScript();
    }

    setTimeout(async () => {
        if (!snapToken) {
            console.error("Snap token is not available");
            return;
        }

        await window.snap.pay(snapToken, {
            onSuccess: async function (result) {
                window.scrollTo({
                    top: 0,
                    behavior: "smooth",
                });

                onChangeStatus("success");
                onSuccess(result);
            },
            onPending: async function (result) {
                onChangeStatus("pending");
                onPending(result);
            },
            onError: async function (result) {
                onChangeStatus("error");
                onError(result);
            },
            onClose: async function () {
                onChangeStatus("error");
                onClose();
            },
        });
    }, delay);
}

async function changePaymentType(
    { transactionCode },
    { onSuccess = (response) => {}, onError = (error) => {} }
) {
    await axios
        .put(
            "/api/change-payment-type",
            {
                transaction_code: transactionCode,
            },
            {
                headers: {
                    authorization: `Bearer ${cookieManager.getItem(
                        "access_token"
                    )}`,
                },
            }
        )
        .then(onSuccess)
        .catch(onError);
}

export default {
    initScript,
    showSnap,
    checkPayment,
    changePaymentType,
};
