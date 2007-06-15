# Filters added to this controller apply to all controllers in the application.
# Likewise, all the methods added will be available for all controllers.

class ApplicationController < ActionController::Base
  include AuthenticatedSystem
  include ExceptionLoggable

  # "remember me" functionality and threadlocal access
  before_filter :login_from_cookie, :set_current_user, :set_active_theme
  after_filter :clear_current_user

  # Pick a unique cookie name to distinguish our session data from others'
  session :session_key => '_planworld.rb_session_id'
  
private

  # Clear the current_user threadlocal after each request to avoid leakage.
  def clear_current_user
    User.current_user = nil
  end

  def set_active_theme
    @theme = Theme.find(1)
  end

  # Set the current_user threadlocal to the current user.
  def set_current_user
    if logged_in?
      User.current_user = current_user
    else
      User.current_user = nil
    end
  end
end
