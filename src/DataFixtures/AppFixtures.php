<?php

namespace App\DataFixtures;

use App\Entity\AboutUs;
use App\Entity\Banner;
use App\Entity\Blog;
use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Details;
use App\Entity\HomeBlog;
use App\Entity\PersonalInfo;
use App\Entity\Product;
use App\Entity\SecondBanner;
use App\Entity\Size;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $projectDir = dirname(__DIR__, 2);

        // ── Admin User ──
        $admin = new User();
        $admin->setEmail('admin@algolus.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('Algolus');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // ── Regular User ──
        $user = new User();
        $user->setEmail('user@algolus.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setCountry('France');
        $user->setAddress('123 Rue de Paris');
        $user->setCity('Paris');
        $user->setPostCode('75001');
        $manager->persist($user);

        // ── Colors ──
        $colorNames = ['Red', 'Blue', 'Green', 'Black', 'White', 'Brown'];
        $colors = [];
        foreach ($colorNames as $name) {
            $color = new Color();
            $color->setColorName($name);
            $manager->persist($color);
            $colors[] = $color;
        }

        // ── Sizes ──
        $sizeNames = ['XS', 'S', 'M', 'L', 'XL'];
        $sizes = [];
        foreach ($sizeNames as $name) {
            $size = new Size();
            $size->setSizeName($name);
            $manager->persist($size);
            $sizes[] = $size;
        }

        // ── Categories ──
        $categoryData = [
            ['name' => 'Pastries', 'image' => 'category-1.png'],
            ['name' => 'Cakes', 'image' => 'category-2.png'],
            ['name' => 'Breads', 'image' => 'category-3.png'],
            ['name' => 'Cookies', 'image' => 'category-4.png'],
        ];
        $categories = [];
        $catImgSrc = $projectDir . '/public/assets/images/banner/';
        $catImgDst = $projectDir . '/public/uploads/category/';
        @mkdir($catImgDst, 0777, true);
        foreach ($categoryData as $data) {
            if (file_exists($catImgSrc . $data['image'])) {
                copy($catImgSrc . $data['image'], $catImgDst . $data['image']);
            }
            $cat = new Category();
            $cat->setCategoryName($data['name']);
            $cat->setCategoryImage($data['image']);
            $manager->persist($cat);
            $categories[] = $cat;
        }

        // ── Products ──
        $productImages = [
            'pro-hm1-1.jpg', 'pro-hm1-2.jpg', 'pro-hm1-3.jpg', 'pro-hm1-4.jpg',
            'pro-hm1-5.jpg', 'pro-hm1-6.jpg', 'pro-hm1-7.jpg', 'pro-hm1-8.jpg',
            'pro-hm2-1.jpg', 'pro-hm2-2.jpg', 'pro-hm2-3.jpg', 'pro-hm2-4.jpg',
        ];
        $productNames = [
            'Napoleon Hat', 'Parkin Cake', 'Chocolate Croissant', 'Almond Tart',
            'Vanilla Eclair', 'Fruit Pie', 'Cinnamon Roll', 'Blueberry Muffin',
            'Caramel Brownie', 'Lemon Drizzle Cake', 'Red Velvet Cupcake', 'Tiramisu',
        ];
        $productDescriptions = [
            'A classic French pastry with layers of puff and cream.',
            'Traditional British cake with oatmeal, ginger, and treacle.',
            'Flaky butter croissant filled with rich dark chocolate.',
            'Delicate tart with frangipane and toasted almonds.',
            'Light choux pastry filled with vanilla custard.',
            'Homemade pie loaded with seasonal fresh fruits.',
            'Soft and fluffy roll swirled with cinnamon sugar.',
            'Moist muffin packed with juicy wild blueberries.',
            'Dense chocolate brownie with caramel swirl topping.',
            'Light sponge soaked in tangy lemon glaze.',
            'Rich cream cheese frosted red velvet cupcake.',
            'Italian espresso-soaked ladyfingers with mascarpone cream.',
        ];

        $prodImgSrc = $projectDir . '/public/assets/images/product/';
        $prodImgDst = $projectDir . '/public/uploads/product/';
        @mkdir($prodImgDst, 0777, true);

        for ($i = 0; $i < 12; $i++) {
            $imgFile = $productImages[$i];
            if (file_exists($prodImgSrc . $imgFile)) {
                copy($prodImgSrc . $imgFile, $prodImgDst . $imgFile);
            }
            $product = new Product();
            $product->setProductName($productNames[$i]);
            $product->setProductPrice(round(mt_rand(500, 3500) / 100, 2));
            $product->setProductImage($imgFile);
            $product->setProductTaxe(round(mt_rand(5, 20) / 100, 2));
            $product->setProductDescription($productDescriptions[$i]);
            $product->setCategory($categories[$i % count($categories)]);
            $product->setOnSale($i % 3 === 0);
            $product->addColor($colors[$i % count($colors)]);
            $product->addColor($colors[($i + 1) % count($colors)]);
            $product->addSize($sizes[$i % count($sizes)]);
            $product->addSize($sizes[($i + 1) % count($sizes)]);
            $manager->persist($product);

            // Stock details
            $detail = new Details();
            $detail->setColor($colors[$i % count($colors)]->getColorName());
            $detail->setSize($sizes[$i % count($sizes)]->getSizeName());
            $detail->setQty(mt_rand(5, 50));
            $detail->setProduct($product);
            $manager->persist($detail);
        }

        // ── Banner ──
        $bannerImg = 'hm1-bg-1.jpg';
        $bannerDst = $projectDir . '/public/uploads/banner/';
        @mkdir($bannerDst, 0777, true);
        $bannerSrc = $projectDir . '/public/assets/images/slider/' . $bannerImg;
        if (file_exists($bannerSrc)) {
            copy($bannerSrc, $bannerDst . $bannerImg);
        }
        $banner = new Banner();
        $banner->setSupTitle('Welcome to Algolus');
        $banner->setTitle('Artisan Bakery & Pastries');
        $banner->setDescription('Handcrafted with love, baked fresh every day.');
        $banner->setImage($bannerImg);
        $manager->persist($banner);

        // ── Second Banner ──
        $secondBannerImg = 'bg-1.jpg';
        $secondBannerDst = $projectDir . '/public/uploads/secondBanner/';
        @mkdir($secondBannerDst, 0777, true);
        $secondBannerSrc = $projectDir . '/public/assets/images/bg/' . $secondBannerImg;
        if (file_exists($secondBannerSrc)) {
            copy($secondBannerSrc, $secondBannerDst . $secondBannerImg);
        }
        $secondBanner = new SecondBanner();
        $secondBanner->setTitle('Special Offers This Week');
        $secondBanner->setDescription('Up to 30% off on selected pastries and cakes.');
        $secondBanner->setImage($secondBannerImg);
        $manager->persist($secondBanner);

        // ── Blog posts ──
        $blogSrc = $projectDir . '/public/assets/images/banner/';
        $blogDst = $projectDir . '/public/uploads/blog/';
        @mkdir($blogDst, 0777, true);
        $blogData = [
            ['title' => 'The Art of French Pastry', 'desc' => 'Discover the secrets behind our handcrafted croissants and éclairs.', 'img' => 'banner-2.png'],
            ['title' => 'Top 5 Cakes for Every Occasion', 'desc' => 'From birthdays to weddings, these cakes steal the show.', 'img' => 'banner-3.png'],
            ['title' => 'Healthy Baking Tips', 'desc' => 'Learn how to make delicious treats with wholesome ingredients.', 'img' => 'banner-4.png'],
        ];
        foreach ($blogData as $data) {
            if (file_exists($blogSrc . $data['img'])) {
                copy($blogSrc . $data['img'], $blogDst . $data['img']);
            }
            $blog = new Blog();
            $blog->setTitle($data['title']);
            $blog->setDescription($data['desc']);
            $blog->setImage($data['img']);
            $manager->persist($blog);
        }

        // ── About Us ──
        $aboutUsDst = $projectDir . '/public/uploads/aboutUs/';
        @mkdir($aboutUsDst, 0777, true);
        $aboutUsImgSrc = $projectDir . '/public/assets/images/banner/about-us.jpg';
        if (file_exists($aboutUsImgSrc)) {
            copy($aboutUsImgSrc, $aboutUsDst . 'about-us.jpg');
        }
        $aboutUs = new AboutUs();
        $aboutUs->setTitle('About Algolus');
        $aboutUs->setDescription('We are a family-run bakery passionate about quality ingredients and traditional recipes.');
        $aboutUs->setContent('Founded in 2020, Algolus brings together the finest baking traditions with modern creativity. Every product is made fresh daily using locally sourced ingredients. Our mission is to bring joy to every table with our artisan breads, pastries, and cakes.');
        $aboutUs->setImage('about-us.jpg');
        $manager->persist($aboutUs);

        // ── Home Blog ──
        $homeBlogDst = $projectDir . '/public/uploads/homeBlog/';
        @mkdir($homeBlogDst, 0777, true);
        $homeBlogImgSrc = $projectDir . '/public/assets/images/banner/banner-1.jpg';
        if (file_exists($homeBlogImgSrc)) {
            copy($homeBlogImgSrc, $homeBlogDst . 'banner-1.jpg');
        }
        $homeBlog = new HomeBlog();
        $homeBlog->setHomeTitle('From Our Kitchen');
        $homeBlog->setHomeDescription('Tips, recipes, and stories from our bakers.');
        $homeBlog->setHomeContent('Follow our blog for the latest baking inspiration and behind-the-scenes looks at our bakery.');
        $homeBlog->setHomeImage('banner-1.jpg');
        $manager->persist($homeBlog);

        // ── Personal Info (site contact details) ──
        $info = new PersonalInfo();
        $info->setEmail('contact@algolus.com');
        $info->setAddress('123 Baker Street, Paris, France');
        $info->setPhone('+33 1 23 45 67 89');
        $info->setContent('Open Monday-Saturday, 7AM-8PM. Closed Sundays.');
        $manager->persist($info);

        $manager->flush();
    }
}
