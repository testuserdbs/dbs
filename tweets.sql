CREATE TABLE tweet(tweet_id int NOT NULL, favorite_count int, retweet_count int, t_time int, text varchar, CONSTRAINT tweet_pk PRIMARY KEY (tweet_id) );
CREATE TABLE hashtag(h_time int, h_name varchar NOT NULL, CONSTRAINT hashtag_pk PRIMARY KEY (h_name));
CREATE TABLE hat_einen(tweet_id int,h_name varchar, CONSTRAINT hat_einen_pk PRIMARY KEY (tweet_id, h_name));
ALTER TABLE hat_einen ADD FOREIGN KEY (h_name) REFERENCES hashtag (h_name);
ALTER TABLE hat_einen ADD FOREIGN KEY (tweet_id) REFERENCES tweet (tweet_id);
# dbs
