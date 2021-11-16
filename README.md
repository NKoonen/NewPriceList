# New Price List Import for Prestashop

This script lets you update prices based on reference of **Prestashop 1.7**. This also includes the combination products. It updates the prices according to the end price that is uploaded with a csv file. If you want to update these prices in **Prestashop 1.6** check: [Free module](https://www.prestashop.com/forums/topic/521717-free-module-new-price-list/). 

# How to use

Place the file 'NewPriceImport.php' in the root of your webshop. Then visit your the page yourdomain.com/NewPriceImport.php . Here you can upload a single file. This file has to have a **.CSV** extension. Once uploaded the script will update all the prices of simple and combination products. Just as a safety feature, when you are done you should delete the file from your server again.

## How does the CSV file look?

The first column of the file should only contain all the references (simple or combination products). The second column should only contain the NEW price and use a comma seperated or if the checkbox is selected it accepts semicolon. That is it, nothing more, nothing less. This means, you don't have to think about the original 'impact of price' what Prestashop uses with combination products. You only use the final price in the csv file.
