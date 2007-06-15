class CoreController < ApplicationController
  before_filter :login_required
  
  def finger
    @user = User.find_by_username(params[:username], :include => :plan)
    @plan = @user.plan
  end
end
