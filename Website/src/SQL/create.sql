DROP TABLE IF EXISTS GroupMembers;
DROP TABLE IF EXISTS Carshare;
DROP TABLE IF EXISTS Review;
DROP TABLE IF EXISTS UserPreferences;
DROP TABLE IF EXISTS InterestedIn;
DROP TABLE IF EXISTS User;
DROP TABLE IF EXISTS EventTag;
DROP TABLE IF EXISTS Event;
DROP TABLE IF EXISTS VenueTag;
DROP TABLE IF EXISTS Venue;
DROP TABLE IF EXISTS Tag;
DROP TABLE IF EXISTS VenueUser;

CREATE TABLE VenueUser (
    VenueUserID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    VenueUserEmail VARCHAR(255) NOT NULL,
    VenueUserPass VARCHAR(255) NOT NULL,
    VenueUserName VARCHAR(255) NOT NULL,
    VenueUserExternal VARCHAR (255),
    PRIMARY KEY (VenueUserID)
);

CREATE TABLE Tag (
    TagID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    TagName VARCHAR(255) NOT NULL,
    PRIMARY KEY (TagID)
);

CREATE TABLE Venue (
    VenueID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    VenueUserID INT(10) NOT NULL,
    VenueName VARCHAR(255) NOT NULL,
    VenueDescription VARCHAR(255) NOT NULL,
    VenueImage VARCHAR(255),
    VenueAddress VARCHAR(255) NOT NULL,
    VenueTimes VARCHAR(255),
    PRIMARY KEY (VenueID),
    FOREIGN KEY (VenueUserID) REFERENCES VenueUser(VenueUserID)
);

CREATE TABLE VenueTag (
    VenueTagID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    VenueID INT(10) NOT NULL,
    TagID INT(10) NOT NULL,
    PRIMARY KEY (VenueTagID),
    FOREIGN KEY (VenueID) REFERENCES Venue(VenueID),
    FOREIGN KEY (TagID) REFERENCES Tag(TagID)
);

CREATE TABLE Event (
    EventID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    VenueID INT(10) NOT NULL,
    EventName VARCHAR(255) NOT NULL,
    EventDescription VARCHAR(255) NOT NULL,
    EventImage VARCHAR(255),
    EventStartTime DATETIME NOT NULL,
    EventEndTime DATETIME NOT NULL,
    PRIMARY KEY (EventID),
    FOREIGN KEY (VenueID) REFERENCES Venue(VenueID)
);

CREATE TABLE EventTag (
    EventTagID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    EventID INT(10) NOT NULL,
    TagID INT(10) NOT NULL,
    PRIMARY KEY (EventTagID),
    FOREIGN KEY (EventID) REFERENCES Event(EventID),
    FOREIGN KEY (TagID) REFERENCES Tag(TagID)
);

CREATE TABLE User (
    UserID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    UserEmail VARCHAR(255) NOT NULL,
    UserPass VARCHAR(255) NOT NULL,
    UserDOB DATE NOT NULL,
    UserLocation VARCHAR(255),
    IsAdmin BOOLEAN NOT NULL,
    IsVerified BOOLEAN DEFAULT '1' NOT NULL,
    VerifyHash VARCHAR(32) NOT NULL,
    PRIMARY KEY (UserID)
);

CREATE TABLE InterestedIn (
    InterestedID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    UserID INT(10) NOT NULL,
    EventID INT(10) NOT NULL,
    PRIMARY KEY (InterestedID),
    FOREIGN KEY (UserID) REFERENCES User(UserID),
    FOREIGN KEY (EventID) REFERENCES Event(EventID)
);

CREATE TABLE UserPreferences (
    PreferenceID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    UserID INT(10) NOT NULL,
    TagID INT(10) NOT NULL,
    PRIMARY KEY (PreferenceID),
    FOREIGN KEY (UserID) REFERENCES User(UserID),
    FOREIGN KEY (TagID) REFERENCES Tag(TagID)
);

CREATE TABLE Review (
    ReviewID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    VenueID INT(10) NOT NULL,
    EventID INT(10) NOT NULL,
    UserID INT(10) NOT NULL,
    ReviewDate DATE NOT NULL,
    -- Currently set ReviewText as NVARCHAR(max) which gives it up to 2GB of text apparently and supports unicode
    -- Communicate with everyone about character limit on the text input to specify this value here
    ReviewText NVARCHAR(500),
    ReviewPrice INT NOT NULL,
    ReviewQuality INT NOT NULL,
    ReviewSafety INT NOT NULL,
    ReviewQueue INT NOT NULL,
    PRIMARY KEY (ReviewID),
    FOREIGN KEY (VenueID) REFERENCES Venue(VenueID),
    FOREIGN KEY (EventID) REFERENCES Event(EventID),
    FOREIGN KEY (UserID) REFERENCES User(UserID)
);

CREATE TABLE Carshare (
    CarshareID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    GroupOwner INT(10) NOT NULL,
    GroupSize INT(2) NOT NULL,
    MeetingLocation VARCHAR(255) NOT NULL,
    Destination VARCHAR(255) NOT NULL,
    MeetingTime DATETIME NOT NULL,
    Active BOOLEAN NOT NULL,
    PRIMARY KEY (CarshareID),
    FOREIGN KEY (GroupOwner) REFERENCES User(UserID)
);

CREATE TABLE GroupMembers (
    GroupID INT(10) NOT NULL UNIQUE AUTO_INCREMENT,
    CarshareID INT(10) NOT NULL,
    MemberID INT(10) NOT NULL,
    PRIMARY KEY (GroupID),
    FOREIGN KEY (CarshareID) REFERENCES Carshare(CarshareID),
    FOREIGN KEY (MemberID) REFERENCES User(UserID)
);

INSERT INTO User (UserEmail,UserPass,UserDOB,IsAdmin,VerifyHash) VALUES ('test@test.com','testtest1','2000-02-02',false,'33e75ff09dd601bbe69f351039152189');
