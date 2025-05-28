-- Sample Destinations Data
INSERT INTO destinations (destination_name, destination_img, destination_desc) VALUES
(
    'Bali, Indonesia',
    'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800',
    'Experience the perfect blend of pristine beaches, spiritual temples, and vibrant culture in this tropical paradise.'
),
(
    'Santorini, Greece',
    'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?auto=format&fit=crop&w=800',
    'Discover whitewashed buildings, blue-domed churches, and stunning sunsets over the Aegean Sea.'
),
(
    'Machu Picchu, Peru',
    'https://images.unsplash.com/photo-1587595431973-160d0d94add1?auto=format&fit=crop&w=800',
    'Explore the ancient Incan citadel set high in the Andes Mountains, surrounded by mystical clouds.'
),
(
    'Maldives',
    'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=800',
    'Indulge in luxury overwater villas, crystal-clear waters, and world-class snorkeling in this island paradise.'
),
(
    'Venice, Italy',
    'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=800',
    'Navigate through romantic canals, historic architecture, and charming bridges in this floating city.'
),
(
    'Kyoto, Japan',
    'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?auto=format&fit=crop&w=800',
    'Immerse yourself in traditional Japanese culture with ancient temples, serene gardens, and historic geisha districts.'
),
(
    'Paris, France',
    'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=800',
    'Fall in love with the City of Light, featuring iconic landmarks, world-class cuisine, and timeless art and culture.'
),
(
    'Dubai, UAE',
    'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=800',
    'Experience the future today in this ultra-modern city with towering skyscrapers, luxury shopping, and desert adventures.'
),
(
    'Great Barrier Reef, Australia',
    'https://images.unsplash.com/photo-1582434221241-50dad29dab89?auto=format&fit=crop&w=800',
    'Dive into the world\'s largest coral reef system, home to vibrant marine life and stunning underwater landscapes.'
),
(
    'Banff National Park, Canada',
    'https://images.unsplash.com/photo-1609863539625-1ee649aea0e4?auto=format&fit=crop&w=800',
    'Discover the majestic Canadian Rockies with pristine lakes, snow-capped peaks, and abundant wildlife.'
),
(
    'Cape Town, South Africa',
    'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?auto=format&fit=crop&w=800',
    'Where mountains meet the ocean, offering diverse experiences from wine tasting to penguin watching.'
),
(
    'Petra, Jordan',
    'https://images.unsplash.com/photo-1563177978-4c5ebf35c1fd?auto=format&fit=crop&w=800',
    'Step back in time at this ancient rose-colored city, carved into rock faces and filled with archaeological wonders.'
);

-- Create destinations directory if it doesn't exist
-- Note: This needs to be run in PHP, not SQL
-- mkdir -p ../assets/images/destinations

-- Sample Hotels Data
INSERT INTO hotels (hotel_name, hotel_location, star_rating, price, hotel_img) VALUES
('The Ritz-Carlton Bali', 'Nusa Dua, Bali, Indonesia', 5, 320.00, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800'),
('Canaves Oia Suites', 'Oia, Santorini, Greece', 5, 450.00, 'https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=800'),
('Belmond Sanctuary Lodge', 'Machu Picchu, Peru', 4, 380.00, 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=800'),
('Soneva Fushi', 'Baa Atoll, Maldives', 5, 1200.00, 'https://images.unsplash.com/photo-1519821172143-ecb1df1bbf41?auto=format&fit=crop&w=800'),
('Hotel Danieli', 'Venice, Italy', 5, 600.00, 'https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=800'),
('The Ritz-Carlton Kyoto', 'Kyoto, Japan', 5, 700.00, 'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=800'),
('Le Meurice', 'Paris, France', 5, 850.00, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800'),
('Burj Al Arab', 'Dubai, UAE', 7, 2000.00, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=800'),
('Qualia Resort', 'Hamilton Island, Australia', 5, 950.00, 'https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=800'),
('Fairmont Banff Springs', 'Banff, Canada', 4, 400.00, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800'),
('One&Only Cape Town', 'Cape Town, South Africa', 5, 500.00, 'https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=800'),
('Mövenpick Resort Petra', 'Petra, Jordan', 5, 350.00, 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=800');
