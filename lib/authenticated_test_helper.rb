module AuthenticatedTestHelper
  # Sets the current user in the session from the user fixtures.
  def login_as(user)
    @request.session[:user] = user ? users(user).id : nil
  end
  
  def logout
    login_as(nil)
  end

  def content_type(type)
    @request.env['Content-Type'] = type
  end

  def accept(accept)
    @request.env["HTTP_ACCEPT"] = accept
  end

  def authorize_as(user)
    if user
      @request.env["HTTP_AUTHORIZATION"] = "Basic #{Base64.encode64("#{users(user).username}:test")}"
      accept       'application/xml'
      content_type 'application/xml'
    else
      @request.env["HTTP_AUTHORIZATION"] = nil
      accept       nil
      content_type nil
    end
  end

  # Assert the block redirects to the login
  # 
  #   assert_requires_login(:bob) { |c| c.get :edit, :id => 1 }
  #
  def assert_requires_login(username = nil)
    yield HttpLoginProxy.new(self, username)
  end

  def assert_http_authentication_required(username = nil)
    yield XmlLoginProxy.new(self, username)
  end

  def reset!(*instance_vars)
    instance_vars = [:controller, :request, :response] unless instance_vars.any?
    instance_vars.collect! { |v| "@#{v}".to_sym }
    instance_vars.each do |var|
      instance_variable_set(var, instance_variable_get(var).class.new)
    end
  end
end

class BaseLoginProxy
  attr_reader :controller
  attr_reader :options
  def initialize(controller, username)
    @controller = controller
    @username   = username
  end

  private
    def authenticated
      raise NotImplementedError
    end
    
    def check
      raise NotImplementedError
    end
    
    # SF: Changed s.t. behavior is:
    #  try to execute method
    #  make sure that login was required
    #  login
    #  execute the method again, logged in
    def method_missing(method, *args)
      @controller.reset!
      @controller.send(method, *args)
      check
      @controller.reset!
      authenticate
      @controller.send(method, *args)
    end
end

class HttpLoginProxy < BaseLoginProxy
  protected
    def authenticate
      @controller.login_as @username if @username
    end
    
    def check
      @controller.assert_redirected_to :controller => 'sessions', :action => 'new'
    end
end

class XmlLoginProxy < BaseLoginProxy
  protected
    def authenticate
      @controller.accept 'application/xml'
      @controller.authorize_as @username if @username
    end
    
    def check
      @controller.assert_response 401
    end
end
