/**
 * Decode data from a base64 image.
 *
 * @param {string} base64Image - The base64 encoded image to decode data from.
 * @returns {Promise} A Promise that resolves with the decoded data or rejects with an error.
 */
function decodeDataFromImage(base64Image) {
    return new Promise((resolve, reject) => {
        var img = new Image();
        img.src = base64Image;

        img.onload = () => {
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height;
            context.drawImage(img, 0, 0);

            var imageData = context.getImageData(0, 0, img.width, img.height);
            var data = imageData.data; // RGBA array

            let binaryData = '';
            for (let i = 0; i < data.length; i += 4) {
                // Extract least significant bit of each color channel
                binaryData += (data[i] & 1).toString(); // Red channel
                binaryData += (data[i + 1] & 1).toString(); // Green channel
                binaryData += (data[i + 2] & 1).toString(); // Blue channel
            }

            // Extract the length of the data
            var dataLengthBinary = binaryData.substring(0, 32);
            var dataLength = parseInt(dataLengthBinary, 2);

            // Extract and convert binary data to ASCII text
            let result = '';
            for (let i = 32; i < 32 + dataLength * 8; i += 8) {
                let byte = binaryData.substring(i, i + 8);
                let charCode = parseInt(byte, 2);
                result += String.fromCharCode(charCode);
            }

            resolve(result);
        };

        img.onerror = () => reject(new Error('Image load error'));
    });
}
