## Description
Optimize Images via Console Call.   
Uses the beautiful package from [spatie/image-optimize](https://github.com/spatie/image-optimizer) 

## Installation

Debian/Ubuntu:
```bash
sudo apt-get install jpegoptim
sudo apt-get install optipng
sudo apt-get install pngquant
sudo apt-get install gifsicle
```

MacOS:
```bash
brew install jpegoptim
brew install optipng
brew install pngquant
brew install gifsicle
```

Add this Package to your Contao 4.* Installation:  
```
composer require nerdlichter/contao-image-optimizer-bundle
```

### Console Usage
###### Single File
```bash
➜  contao-source cc nl-image:optimize --path="assets/images/5/Karibik.jpg" --dry-run
Performing dry run! No Changes will be made
Optimizing assets/images/5/Karibik.jpg
Total Bytes: 13165 / 13104
Change Percentage: 99.54%
```
###### Path
```bash
➜  contao-source cc nl-image:optimize --path="assets/images/5/" --dry-run
Performing dry run! No Changes will be made
Using path "assets/images/5/"
Found 4 images
Optimizing /Users/max/dev/demo/assets/images/5/slider_big-c-b20ea066.png
Optimizing /Users/max/dev/demo/assets/images/5/slider_big-c-9d285542.jpg
Optimizing /Users/max/dev/demo/assets/images/5/contao_extensions-f643ddd6.png
Total Bytes: 156292 / 66626
Change Percentage: 42.63%
```

###### Backup
```bash
➜  contao-source cc nl-image:optimize --path="assets/images/5/" --backup
Using path "assets/images/5/"
Found 4 images
Optimizing /Users/max/dev/demo/assets/images/5/slider_big-c-b20ea066.png
Created Backup /Users/max/dev/demo/assets/images/5/slider_big-c-b20ea066.png.original
Optimizing /Users/max/dev/demo/assets/images/5/slider_big-c-9d285542.jpg
Created Backup /Users/max/dev/demo/assets/images/5/slider_big-c-9d285542.jpg.original
Optimizing /Users/max/dev/demo/assets/images/5/contao_extensions-f643ddd6.png
Created Backup /Users/max/dev/demo/assets/images/5/contao_extensions-f643ddd6.png.original
Total Bytes: 156292 / 66626
Change Percentage: 42.63%
```
