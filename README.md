# duplicateimagefinder
A simple PHP script that finds duplicate photos in a a given directory. 

## Usage

In this sample usage, an execution memory limit of 4G is invoked because this is currently memory hungry (opened image files are cached in memory to prevent frequent calls to the PHP GD `imagecreatefrom...` functions)

```
php -d memory_limit=4096M finder.php  -d <input dir> -t <threshold percent>

php -d memory_limit=4096M finder.php  -d testfolder -t 60
```

## Algorithm
Files are recursively fetched in the given directory. Each image file is compared to one another by:
1. Downsampling them to 32px
1. Comparing their dimension (10% of the score)
2. Comparing their color (90% of the score)

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
This is a naïve solution written as a quick workaround to paid apps. The objective of this solution is to find almost exact matches. No AI is involved.

A more robust approach to image similarity should also take into a account not just the colors in the same positions, but also whether the objects in the image has shifted (the colors will not match in the same coordinates). This will require a more advanced solution.

```
.----------.  .----------.
|          |  |   A      |
| A        |  |     B    |
|  B       |  |      C   |
|   C      |  |          |
'----------'  '----------'
```
