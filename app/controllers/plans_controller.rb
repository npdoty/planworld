class PlansController < CRUDController
  before_filter :login_required
end
