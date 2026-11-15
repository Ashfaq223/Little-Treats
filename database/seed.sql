-- Optional starter data. Customers, orders and admin accounts are NOT included.
-- Create the first admin with the command shown in README.md.

INSERT INTO `categories` (`category_name`) VALUES
('Cakes'), ('Cupcakes'), ('Cookies'), ('Desserts'), ('Brownies & Bars');

INSERT INTO `loyalty_rewards` (`reward_name`, `points_required`, `description`) VALUES
('6-Piece Brownie Box', 10, 'A box containing 6 delicious brownies');