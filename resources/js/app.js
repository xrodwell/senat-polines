import { Html5Qrcode } from 'html5-qrcode';
import QRCode from 'qrcode';
import jsQR from 'jsqr';

window.Html5Qrcode = Html5Qrcode;
window.QRCode = QRCode;
window.jsQR = jsQR;

window.renderQrCode = function(canvas, text, options = {}) {
    if (!canvas || !text) return;
    const opts = Object.assign({ width: 280, margin: 2 }, options);
    try {
        if (QRCode && typeof QRCode.toCanvas === 'function') {
            QRCode.toCanvas(canvas, text, opts, function(err) {
                if (err) console.error('QRCode canvas error:', err);
            });
        } else if (QRCode && QRCode.default && typeof QRCode.default.toCanvas === 'function') {
            QRCode.default.toCanvas(canvas, text, opts, function(err) {
                if (err) console.error('QRCode.default canvas error:', err);
            });
        }
    } catch (e) {
        console.error('renderQrCode exception:', e);
    }
};

window.decodeQrFromImage = async function(fileOrBlob) {
    if (!fileOrBlob) return null;

    if ('BarcodeDetector' in window) {
        try {
            const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            const bitmap = await createImageBitmap(fileOrBlob);
            const barcodes = await detector.detect(bitmap);
            if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                return barcodes[0].rawValue;
            }
        } catch (e) {
            console.warn('Native BarcodeDetector skipped:', e);
        }
    }

    const img = new Image();
    const url = URL.createObjectURL(fileOrBlob);
    try {
        await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = reject;
            img.src = url;
        });

        if (window.jsQR) {
            const scales = [800, 1200, Math.max(img.width, img.height)];
            for (const targetDim of scales) {
                let w = img.width;
                let h = img.height;
                if (w > targetDim || h > targetDim) {
                    if (w > h) {
                        h = Math.round((h * targetDim) / w);
                        w = targetDim;
                    } else {
                        w = Math.round((w * targetDim) / h);
                        h = targetDim;
                    }
                }
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);
                const imgData = ctx.getImageData(0, 0, w, h);
                const res = window.jsQR(imgData.data, w, h, { inversionAttempts: 'attemptBoth' });
                if (res && res.data) {
                    URL.revokeObjectURL(url);
                    return res.data;
                }
            }
        }
    } finally {
        try { URL.revokeObjectURL(url); } catch (_) {}
    }

    return null;
};
