# Implements planworld.net-style authentication using a separate account_data table
module PlanworldNetAuthentication
  def self.extended(obj)
    # extend the object with the following
    obj.class_eval do
      has_one :account_data

      delegate :crypted_password,           :to => :account_data
      delegate :crypted_password=,          :to => :account_data
      delegate :remember_token,             :to => :account_data
      delegate :remember_token=,            :to => :account_data
      delegate :remember_token_expires_at,  :to => :account_data
      delegate :remember_token_expires_at=, :to => :account_data
      delegate :salt,                       :to => :account_data
      delegate :salt=,                      :to => :account_data
      
      # Authenticates a user by their username and unencrypted password.  Returns the user or nil.
      def self.authenticate(username, password)
        return nil if password.empty?

        u = find(:first, :conditions => ['username = ?', username]) # need to get the salt
        return u if u && u.authenticated?(password)

        # fall back to a legacy login
        u = find(:first,
             :select => "users.*",
             :conditions => ["users.username = ? AND account_data.Password=OLD_PASSWORD(?)", username, password],
             :joins => "LEFT JOIN account_data ON users.username=account_data.Username")

        # migrate the account since we know their password right now
        if u
          # yes, these are different; the first is virtual, the second actually refers to the database
          u.account_data.password = password
          u.account_data.Password = nil
          u.account_data.save
        end

        u
      end

      # Thread-local access to the currently logged-in User.
      def self.current_user
        Thread.current[:user]
      end

      # Thread-local access to set the newly logged in User.
      def self.current_user=(user)
        Thread.current[:user] = user
      end

      # Encrypts some data with the salt.
      def self.encrypt(password, salt)
        Digest::SHA1.hexdigest("--#{salt}--#{password}--")
      end

      def self.find_by_remember_token(token, options = {})
        account_data = AccountData.find_by_remember_token(token, options.merge(:include => :user))
        account_data ? account_data.user : nil
      end

      def authenticated?(password)
        self.crypted_password == encrypt(password)
      end

      # Encrypts the password with the user salt
      def encrypt(password)
        self.class.encrypt(password, self.salt)
      end

      def forget_me
        self.remember_token_expires_at = nil
        self.remember_token            = nil
        account_data.save(false)
      end

      # These create and unset the fields required for remembering users between browser closes
      def remember_me
        remember_me_for 2.weeks
      end

      def remember_me_for(time)
        remember_me_until time.from_now.utc
      end

      def remember_me_until(time)
        self.remember_token_expires_at = time
        self.remember_token            = encrypt("#{username}--#{remember_token_expires_at}")
        account_data.save(false)
      end

      def remember_token?
        remember_token_expires_at && Time.now.utc < remember_token_expires_at 
      end
    end
  end
end