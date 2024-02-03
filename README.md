# Image Data Encoder/Decoder

![GitHub Stars](https://img.shields.io/github/stars/dehlirious/PixelDataEncoder)
![GitHub Issues](https://img.shields.io/github/issues/dehlirious/PixelDataEncoder)

This JavaScript and PHP project allows you to encode data into an image and decode it back from the image. It provides a simple way to hide data within image files.

## JavaScript Function: `decodeDataFromImage`

### Description

The `decodeDataFromImage` function decodes data from a base64-encoded image. It loads the image, extracts binary data from the least significant bits of the image's color channels, and converts it back to its original form.

## PHP Function: `encodeDataInImage`

### Description

The `encodeDataInImage` function encodes data into an image using PHP's GD library. It can either load an existing image or create a default one if no image is provided. By embedding data into the least significant bits of the image's color channels, it provides a creative way to hide information within images.

