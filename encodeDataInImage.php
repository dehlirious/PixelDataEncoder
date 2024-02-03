<?php

/**
 * Encode data within an image using GD and return it as a base64 string.
 *
 * @param string $data The data to be encoded within the image.
 * @param string|null $inputImagePath The path to an existing input image (optional).
 *
 * @return string The encoded image as a base64 string.
 *
 * @throws Exception If there's an issue with creating or loading the image.
 */
function encodeDataInImage($data, $inputImagePath = null) {
	if ($inputImagePath && file_exists($inputImagePath)) {
		// Load the input image if provided
		$imageFormat = strtolower(pathinfo($inputImagePath, PATHINFO_EXTENSION));

		// Create an image resource based on the image format
		if ($imageFormat === 'png') {
			$image = imagecreatefrompng($inputImagePath);
		} elseif (in_array($imageFormat, ['jpg', 'jpeg'])) {
			$image = imagecreatefromjpeg($inputImagePath);
		} elseif ($imageFormat === 'gif') {
			$image = imagecreatefromgif($inputImagePath);
		} else {
			// Handle unsupported image formats or provide a default option
			echo 'Unsupported image format';
			exit;
		}
	} else {
		// Create a default image if no input image is provided
		$width = $height = 100; // Default size
		$image = imagecreatetruecolor($width, $height);
		$background = imagecolorallocate($image, 255, 255, 255); // White background
		imagefill($image, 0, 0, $background);
	}

	if (!$image) {
		throw new Exception("Failed to create or load the image.");
	}

	// Get image dimensions
	$width = imagesx($image);
	$height = imagesy($image);

	// Convert data length to a 4-byte binary string
	$dataLength = strlen($data);
	$lengthBinary = str_pad(decbin($dataLength), 32, "0", STR_PAD_LEFT); // 4 bytes for length

	// Convert data to binary string
	$binaryData = $lengthBinary; // Start with the length
	for ($i = 0; $i < $dataLength; $i++) {
		$binaryData .= str_pad(decbin(ord($data[$i])), 8, "0", STR_PAD_LEFT);
	}

	// Embed data into the image
	$dataIndex = 0;
	for ($y = 0; $y < $height && $dataIndex < strlen($binaryData); $y++) {
		for ($x = 0; $x < $width && $dataIndex < strlen($binaryData); $x++) {
			// Get current pixel color
			$rgb = imagecolorat($image, $x, $y);
			$colors = imagecolorsforindex($image, $rgb);
			
			// Modify color's least significant bit to store data
			$colors['red'] = ($colors['red'] & 0xFE) | (int)$binaryData[$dataIndex++];
			if ($dataIndex < strlen($binaryData)) {
				$colors['green'] = ($colors['green'] & 0xFE) | (int)$binaryData[$dataIndex++];
			}
			if ($dataIndex < strlen($binaryData)) {
				$colors['blue'] = ($colors['blue'] & 0xFE) | (int)$binaryData[$dataIndex++];
			}

			// Allocate modified color and set pixel
			$color = imagecolorallocate($image, $colors['red'], $colors['green'], $colors['blue']);
			imagesetpixel($image, $x, $y, $color);
		}
	}

	// Capture the modified image to output buffer
	ob_start();
	imagepng($image);
	$encodedImage = ob_get_contents();
	ob_end_clean();

	// Destroy the image resource
	imagedestroy($image);

	// Return the encoded image as a base64 string
	return 'data:image/png;base64,' . base64_encode($encodedImage);
}

?>

<script>
  /**
   * Decode data from a base64 image.
   *
   * @param {string} base64Image - The base64 encoded image to decode data from.
   * @returns {Promise} A Promise that resolves with the decoded data or rejects with an error.
   */
  function decodeDataFromImage3(base64Image) {
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
  				binaryData += (data[i] & 1).toString();	 // Red channel
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
  
  // Usage example:
  //echo encodeDataInImage('your data here', 'DSC00092.JPG');
  var base64Image = '<?php echo encodeDataInImage('your data here', '');?>';
  
  decodeDataFromImage3(base64Image).then(decodedData => {
  	console.log(decodedData); 
  });
</script>
