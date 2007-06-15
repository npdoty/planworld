# Methods added to this helper will be available to all templates in the application.
module ApplicationHelper
  def format_date(date)
    date.to_s(:pw_date_time)
  end
  
  def format_planwatch_date(planwatch_entry)
    date = planwatch_entry.watched_user.last_updated_at
      
    if date.nil?
      "Never"
    else
      date.to_s(:planwatch)
    end
  end
  
  def logout_path
    logout_sessions_path
  end

  def logout_url
    logout_sessions_url
  end
  
  def planwatch
    current_user.planwatch
  end
  
  def planwatch_link(planwatch_entry)
    link_to(h(planwatch_entry.watched_user.username), finger_path(:username => planwatch_entry.watched_user), :class => "planwatch", :title => "Finger #{h(planwatch_entry.watched_user.username)}")
  end
  
  def themes
    []
  end
end
