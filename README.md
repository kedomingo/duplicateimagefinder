# Duplicate Image Finder
A simple PHP script that finds duplicate photos in a a given directory. 

## Usage

```
php finder.php  -d <input dir> -t <threshold percent>

php finder.php  -d testfolder -t 60
```

### Options
* -t, --threshold - Similarity Threshold in percent. Scores lower than this will not be reported as similar
* -d, --dir - Input directory
* -m, --match-priority - Prioritize files with higher similarity instead of prioritizing larger files. This is faulty when you have a high resolution file, and several duplicated smaller files. The smaller files will be matched 100% with each other and will rank higher (will delete the bigger file)
* --move-duplicates - Will remove the duplicates from the input directory and move them to a backup directory
* -o, --out - Where the duplicates will be moved. Required when `--move-duplicates` is specified

## Algorithm
Files are recursively fetched in the given directory. Each image file is compared to one another using 3 phases:

### Phase 1: Duplicate File Detection
1. Get the file sizes from the list, group the files with exactly the same sizes.
2. From groups of 2 or more files, calculate the MD5sum of the files and compare with each other. Mark the files with the same hashes as duplicates

### Phase 2: Total Scene Color
1. Resize the image to a 1x1 pixel, and let GD decide the average color of the pixel.
2. Sort the files based on the pixels' color closeness to the other files - resulting in a list of images with similar scene colors the closer they are in the list. The goal is to have a decreasing closeness value when comparing the first image against the second- up to the last image in the list.
3. Use the user-given threshold to skip images with a closeness value far enough from the current image being checked to save processing power (e.g. When comparing image 1 with 999 other images, given threshold of 80% - if pixel of image 100 is only 79% similar to pixel of image 1, skip images 100-1000 because they will all have a closeness value < 80%)

### Phase 3: Scaled down image
1. From the images with similar scenes from Phase 2, downsample them to 32px
1. Compare their dimension (10% of the score)
2. Compare their color (90% of the score)

### Dimension
Since both images are resized to the same width, the resulting height will be used as contributing factor to the comparison. When 2 images vary in height drastically (e.g. one is in landscape orientation while the other is in portrait), it will lower the total score of the comparison

### Color
Because the images are resized to a tiny 32px wide image, the colors can be compared pixel by pixel. Starting from the top left to the bottom right coordinate of the smaller image, the pixels of the two images being checked are compared for similarity. The closeness of the red, green, and blue values are calculated separately then averaged.

Demo using the reddish hues #f3d2e4 and #f9c7f7:

```
$e = rgb(243,210,228)
$d = rgb(249,199,247)
$r_score = 243 / 249 = 0.9759
$g_score = 199 / 210 = 0.9476
$b_score = 228 / 247 = 0.9231
$rgb_closeness_score = (0.9759 + 0.9476 + 0.9231) / 3 = 0.9489
```

The scores of each pixel compared are averaged to get the total color-comparison score

### Limitations

#### Performance
This is a bad solution because in the worst case, each file is being compared to all other files in the input. A directory with 1,000 files will require at most ~500,000 comparisons. Then between two files, all the pixels are being compared. This is only slightly improved by reducing the dimension of the image. Performance will be worse if the images in the directory are large, say, more than 1mb (when the file has similar sizes, it will be `md5_file`d; the image will be resized from a huge x,xxx px to a 1x1 px image, etc).

#### Accuracy
This is a naïve solution written as a quick workaround to paid apps. The objective of this solution is to find almost exact matches (Resizing, slight cropping, slight color shift). No AI is involved.

A more robust approach to image similarity should also take into a account not just the colors in the same positions, but also whether the objects in the image has shifted (the colors will not match in the same coordinates). This will require a more advanced solution.

```
.----------.  .----------.
|          |  |   A      |
| A        |  |     B    |
|  B       |  |      C   |
|   C      |  |          |
'----------'  '----------'
```
