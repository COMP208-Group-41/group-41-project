DROP TABLE IF EXISTS Review;

CREATE TABLE Review (
    ReviewID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    VenueID INT(10) NOT NULL,
    EventID INT(10) NULL,
    UserID INT(10) NOT NULL,
    ReviewDate DATE NOT NULL,
    -- Currently set ReviewText as NVARCHAR(max) which gives it up to 2GB of text apparently and supports unicode
    -- Communicate with everyone about character limit on the text input to specify this value here
    ReviewText NVARCHAR(1000),
    ReviewPrice INT NOT NULL,
    ReviewQuality INT NOT NULL,
    ReviewSafety INT NOT NULL,
    ReviewQueue INT NOT NULL,
    PRIMARY KEY (ReviewID),
    FOREIGN KEY (VenueID) REFERENCES Venue(VenueID),
    FOREIGN KEY (EventID) REFERENCES Event(EventID),
    FOREIGN KEY (UserID) REFERENCES User(UserID)
);
